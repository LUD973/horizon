/**
 * Façade analytics Horizon — window.fcpAnalytics.
 *
 * N'interroge JAMAIS directement le CMP (tarteaucitron.js) : la seule source
 * de vérité du consentement est window.fcpConsent (façade du lot
 * consentement). N'utilise que la fonction publique et documentée de
 * GoatCounter (window.goatcounter.count()) — aucun moteur de comptage
 * maison.
 *
 * Comportements garantis :
 *   - Sans GOATCOUNTER_ENDPOINT configuré : aucun script injecté, track() est
 *     un no-op silencieux (jamais même mis en file).
 *   - Sans consentement analytics : AUCUNE requête réseau. Les événements de
 *     formulaire sont mis en file (bornée, mémoire uniquement, jamais
 *     persistée) en attendant un premier octroi.
 *   - Retrait du consentement APRÈS un premier octroi (le script a pu être
 *     chargé) : plus aucun nouvel envoi ; la file en cours est invalidée
 *     (vidée) ; les événements produits pendant la fenêtre de retrait sont
 *     abandonnés — jamais rejoués automatiquement à une ré-acceptation dans
 *     la même page (cf. docs/ANALYTICS_GOATCOUNTER.md).
 *   - Échec réseau/bloqueur : aucune exception visible, aucune nouvelle
 *     tentative automatique dans la page (évite une boucle infinie).
 */
(function () {
    'use strict';

    if (window.fcpAnalytics) {
        return; // façade déjà initialisée
    }

    var cfg = window.fcpAnalyticsConfig || {};
    var configured = cfg.configured === true;
    var endpoint = typeof cfg.endpoint === 'string' ? cfg.endpoint : '';
    var scriptUrl = typeof cfg.scriptUrl === 'string' ? cfg.scriptUrl : '';

    var MAX_QUEUE = 20; // file bornée : évite toute accumulation illimitée

    /** @type {Array<{key:?string,name:string,properties:object}>} mémoire de page uniquement */
    var queue = [];

    var everGranted = false; // le consentement a-t-il déjà été accordé une fois sur cette page ?
    var suppressed = false;  // fenêtre de retrait après octroi : on abandonne, on ne met plus en file

    // États du script : not_started -> loading -> loaded | failed.
    var scriptState = 'not_started';

    // Appels en attente du chargement effectif du script GoatCounter (fenêtre
    // transitoire uniquement, entre l'injection et le onload) — GoatCounter,
    // contrairement à Plausible, n'a pas de file d'attente intégrée.
    var pendingCounts = [];

    function hasConsent() {
        return !!(window.fcpConsent
            && typeof window.fcpConsent.hasConsent === 'function'
            && window.fcpConsent.hasConsent('analytics') === true);
    }

    function enqueue(key, name, properties) {
        if (key) {
            // Déduplication : un même événement (par clé) ne reste qu'une
            // fois en file — ex. « viewed » par page, ou « submitted:<id> »
            // par tentative réelle (voir form.js).
            queue = queue.filter(function (item) { return item.key !== key; });
        }
        queue.push({ key: key || null, name: name, properties: properties || {} });
        while (queue.length > MAX_QUEUE) {
            queue.shift(); // taille bornée : évince le plus ancien
        }
    }

    function flushPendingCounts() {
        var items = pendingCounts;
        pendingCounts = [];
        items.forEach(function (vars) {
            try { window.goatcounter.count(vars); } catch (e) { /* jamais bloquant */ }
        });
    }

    function ensureScriptLoaded() {
        if (scriptState === 'loading' || scriptState === 'loaded') {
            return;
        }
        if (scriptState === 'failed') {
            // Politique de nouvelle tentative : AUCUNE relance automatique
            // dans cette page (pas de boucle infinie). Un rechargement de
            // page retentera naturellement. Limite documentée.
            return;
        }
        if (!configured) {
            return;
        }

        scriptState = 'loading';

        try {
            var script = document.createElement('script');
            script.async = true;
            script.setAttribute('data-goatcounter', endpoint);
            script.src = scriptUrl;
            script.onload = function () { scriptState = 'loaded'; flushPendingCounts(); };
            script.onerror = function () { scriptState = 'failed'; pendingCounts = []; };
            (document.head || document.documentElement).appendChild(script);
        } catch (e) {
            scriptState = 'failed'; // aucune exception visible
        }
    }

    /** Construit les variables GoatCounter (modèle path/title, pas de sac de propriétés générique). */
    function buildVars(name, properties) {
        var path = name;
        var keys = properties ? Object.keys(properties) : [];
        if (keys.length > 0) {
            path += '?' + keys.map(function (k) {
                return encodeURIComponent(k) + '=' + encodeURIComponent(String(properties[k]));
            }).join('&');
        }
        return { path: path, title: name, event: true };
    }

    function sendNow(name, properties) {
        try {
            ensureScriptLoaded();
            var vars = buildVars(name, properties);
            if (scriptState === 'loaded' && window.goatcounter && typeof window.goatcounter.count === 'function') {
                window.goatcounter.count(vars);
            } else if (scriptState === 'loading') {
                pendingCounts.push(vars); // rejoué au onload, jamais après un échec
            }
            // scriptState === 'failed' : abandon silencieux, cohérent avec
            // l'absence de relance automatique.
        } catch (e) {
            /* jamais d'exception visible ; formulaire non affecté */
        }
    }

    function flushQueue() {
        var items = queue;
        queue = [];
        items.forEach(function (item) {
            sendNow(item.name, item.properties);
        });
    }

    /** Point d'entrée unique (track/trackOnce y renvoient). */
    function handle(key, name, properties) {
        if (!configured || suppressed) {
            return; // rule 3 / rule 4 : jamais mis en file dans ces deux cas
        }
        if (hasConsent()) {
            sendNow(name, properties);
        } else {
            enqueue(key, name, properties);
        }
    }

    window.fcpAnalytics = {
        /** Événement non déduppliqué (ex. clics de contact — chaque clic compte). */
        track: function (name, properties) {
            handle(null, name, properties || {});
        },

        /** Événement dédupliqué par clé (ex. « viewed » par page, « submitted:<id> » par tentative). */
        trackOnce: function (key, name, properties) {
            handle(key, name, properties || {});
        }
    };

    if (!configured) {
        return; // rien d'autre à faire : jamais de CMP interrogé ici, jamais de script injecté
    }

    if (window.fcpConsent && typeof window.fcpConsent.onChange === 'function') {
        window.fcpConsent.onChange(function (state) {
            var analyticsNow = !!(state && state.analytics === true);
            if (analyticsNow) {
                if (!everGranted) {
                    everGranted = true;
                    flushQueue(); // premier octroi : envoie ce qui attendait, une fois chacun
                }
                suppressed = false; // reprise normale (premier octroi ou ré-acceptation)
            } else if (everGranted) {
                // Retrait réel après un octroi précédent (rule 4).
                suppressed = true;
                queue = []; // invalide la file : rien n'est rejoué à une ré-acceptation ultérieure
            }
            // Sinon : jamais encore accordé et toujours refusé — la file
            // (bornée, dédupliquée) continue d'attendre une éventuelle
            // acceptation plus tard dans la même page.
        });
    }

    // Clics de contact : délégation générique, aucune donnée personnelle
    // transmise (ni href, ni numéro, ni e-mail, ni texte du lien).
    document.addEventListener('click', function (e) {
        var el = e.target;
        while (el && el !== document && el.tagName !== 'A') {
            el = el.parentElement;
        }
        if (!el || el.tagName !== 'A') {
            return;
        }
        var href = el.getAttribute('href') || '';
        if (href.indexOf('tel:') === 0) {
            window.fcpAnalytics.track('phone_clicked', {});
        } else if (href.indexOf('mailto:') === 0) {
            window.fcpAnalytics.track('email_clicked', {});
        } else if (href.indexOf('wa.me') !== -1) {
            window.fcpAnalytics.track('whatsapp_clicked', {});
        }
    }, true);
})();
