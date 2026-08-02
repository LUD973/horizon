/**
 * Façade de consentement Horizon — window.fcpConsent.
 *
 * Ne réimplémente PAS le moteur de bannière/consentement de tarteaucitron.js.
 * S'appuie uniquement sur ses points d'intégration officiels et documentés :
 *   - tarteaucitron.services[key] = { type, js, fallback, ... } : callbacks
 *     rappelées par tarteaucitron à CHAQUE changement d'état (pas seulement
 *     au chargement initial) — c'est le mécanisme officiel pour réagir à un
 *     octroi/retrait de consentement.
 *   - tarteaucitron.job.push(key)                  : enregistrement du service
 *   - tarteaucitron.init({ privacyUrl, ... })       : initialisation officielle
 *   - tarteaucitron.userInterface.openPanel()       : réouverture du panneau
 *     (lien « Gérer mes cookies »)
 *
 * Catégorie « functional » : toujours autorisée (fonctionnalités strictement
 * nécessaires) — n'est enregistrée comme AUCUN service tarteaucitron, jamais
 * interrogée auprès de son moteur.
 *
 * « analytics » et « marketing » sont chacune un service tarteaucitron dédié
 * (« fcp-analytics » / « fcp-marketing »). Leur état est maintenu ICI, dans un
 * objet interne, alimenté UNIQUEMENT par les callbacks officielles ci-dessus
 * — aucun autre script du plugin n'interroge directement tarteaucitron
 * (analytics.js utilise uniquement fcpConsent.hasConsent()).
 *
 * Sans configuration (fcpConsentConfig.configured === false) : deny-by-default
 * pour analytics/marketing, aucune tentative de charger tarteaucitron, aucune
 * erreur.
 */
(function () {
    'use strict';

    if (window.fcpConsent) {
        return; // évite une double initialisation
    }

    var cfg = window.fcpConsentConfig || {};
    // wp_localize_script() convertit toujours les valeurs en chaînes
    // ('1'/'' pour un booléen PHP) : on accepte les deux représentations.
    var configured = cfg.configured === true || cfg.configured === '1';
    var listeners = [];
    var state = { functional: true, analytics: false, marketing: false };

    function notify() {
        listeners.forEach(function (callback) {
            try { callback(state); } catch (e) { /* un abonné en échec n'affecte pas les autres */ }
        });
    }

    function setAndNotify(patch) {
        state = { functional: true, analytics: state.analytics, marketing: state.marketing };
        Object.keys(patch).forEach(function (key) { state[key] = patch[key]; });
        notify();
    }

    window.fcpConsent = {
        /** @param {'functional'|'analytics'|'marketing'} category */
        hasConsent: function (category) {
            if (category === 'functional') {
                return true;
            }
            if (!configured) {
                return false; // deny-by-default : pas de configuration
            }
            return state[category] === true;
        },

        /** Enregistre un abonné ; appelé immédiatement avec l'état courant. */
        onChange: function (callback) {
            if (typeof callback !== 'function') {
                return;
            }
            listeners.push(callback);
            callback(state);
        },

        /** Réouvre le panneau de préférences tarteaucitron (lien « Gérer mes cookies »). */
        openPreferences: function () {
            try {
                if (window.tarteaucitron && window.tarteaucitron.userInterface
                    && typeof window.tarteaucitron.userInterface.openPanel === 'function') {
                    window.tarteaucitron.userInterface.openPanel();
                }
            } catch (e) { /* jamais bloquant */ }
        }
    };

    if (!configured) {
        return; // rien d'autre à faire : tarteaucitron non chargé, aucune erreur possible
    }

    try {
        window.tarteaucitron = window.tarteaucitron || {};
        window.tarteaucitron.services = window.tarteaucitron.services || {};

        window.tarteaucitron.services['fcp-analytics'] = {
            key: 'fcp-analytics',
            type: 'analytic',
            name: 'Analytics',
            needConsent: true,
            cookies: [],
            js: function () { setAndNotify({ analytics: true }); },
            fallback: function () { setAndNotify({ analytics: false }); }
        };
        window.tarteaucitron.services['fcp-marketing'] = {
            key: 'fcp-marketing',
            type: 'ads',
            name: 'Marketing',
            needConsent: true,
            cookies: [],
            js: function () { setAndNotify({ marketing: true }); },
            fallback: function () { setAndNotify({ marketing: false }); }
        };

        window.tarteaucitron.job = window.tarteaucitron.job || [];
        window.tarteaucitron.job.push('fcp-analytics', 'fcp-marketing');

        if (typeof window.tarteaucitron.init === 'function') {
            window.tarteaucitron.init({
                privacyUrl: cfg.privacyUrl || '',
                orientation: 'bottom',
                showAlertSmall: false,
                cookieslist: false,
                closePopup: true,
                showIcon: true,
                DenyAllCta: true,
                highPrivacy: true,
                handleBrowserDNTRequest: false,
                removeCredit: false,
                useExternalCss: false,
                readmoreLink: cfg.privacyUrl || ''
            });
        }
    } catch (e) { /* jamais bloquant pour le parcours de demande */ }
})();
