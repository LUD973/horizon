/**
 * Façade analytics Horizon — window.fcpAnalytics.
 *
 * N'interroge JAMAIS directement le SDK Didomi : la seule source de vérité du
 * consentement est window.fcpConsent (façade du lot Didomi). Ne réimplémente
 * aucun moteur de chargement Plausible — utilise leur shim officiel
 * (window.plausible / .q) qui tolère un script pas encore chargé.
 *
 * Comportements garantis :
 *   - Sans PLAUSIBLE_DOMAIN configuré : aucun script injecté, track() est un
 *     no-op silencieux (jamais même mis en file).
 *   - Sans consentement analytics : AUCUNE requête réseau. Les événements de
 *     formulaire sont mis en file (bornée, mémoire uniquement, jamais
 *     persistée) en attendant un premier octroi.
 *   - Retrait du consentement APRÈS un premier octroi (le script a pu être
 *     chargé) : plus aucun nouvel envoi ; la file en cours est invalidée
 *     (vidée) ; les événements produits pendant la fenêtre de retrait sont
 *     abandonnés — jamais rejoués automatiquement à une ré-acceptation dans
 *     la même page (cf. docs/ANALYTICS_PLAUSIBLE.md).
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
    var domain = typeof cfg.domain === 'string' ? cfg.domain : '';
    var scriptUrl = typeof cfg.scriptUrl === 'string' ? cfg.scriptUrl : '';

    var MAX_QUEUE = 20; // file bornée : évite toute accumulation illimitée

    /** @type {Array<{key:?string,name:string,properties:object}>} mémoire de page uniquement */
    var queue = [];

    var everGranted = false; // le consentement a-t-il déjà été accordé une fois sur cette page ?
    var suppressed = false;  // fenêtre de retrait après octroi : on abandonne, on ne met plus en file

    // États du script : not_started -> loading -> loaded | failed.
    var scriptState = 'not_started';

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

        // Shim officiel Plausible : tolère un script pas encore chargé en
        // mettant les appels en file interne (window.plausible.q).
        window.plausible = window.plausible || function () {
            (window.plausible.q = window.plausible.q || []).push(arguments);
        };

        try {
            var script = document.createElement('script');
            script.defer = true;
            script.setAttribute('data-domain', domain);
            script.src = scriptUrl;
            script.onload = function () { scriptState = 'loaded'; };
            script.onerror = function () { scriptState = 'failed'; };
            (document.head || document.documentElement).appendChild(script);
        } catch (e) {
            scriptState = 'failed'; // aucune exception visible
        }
    }

    function sendNow(name, properties) {
        try {
            ensureScriptLoaded();
            if (typeof window.plausible !== 'function') {
                return;
            }
            if (properties && Object.keys(properties).length > 0) {
                window.plausible(name, { props: properties });
            } else {
                window.plausible(name);
            }
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
        return; // rien d'autre à faire : jamais de SDK Didomi interrogé ici, jamais de script injecté
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
