/**
 * Façade de consentement Horizon — window.fcpConsent.
 *
 * Ne réimplémente PAS le moteur de chargement Didomi. S'appuie uniquement sur
 * leurs points d'intégration officiels (piles tampon consommées dès que leur
 * SDK est prêt, tolérantes à un SDK absent, lent ou non configuré) :
 *   - window.didomiOnReady        : callback(Didomi) à l'état initial
 *   - window.didomiEventListeners : { event: 'consent.changed', listener }
 *   - Didomi.getUserConsentStatusForPurpose(purposeId)
 *   - Didomi.getUserConsentStatusForVendor(vendorId)
 *   - Didomi.preferences.show()   : réouverture du panneau (« Gérer mes cookies »)
 *
 * Catégorie « functional » : toujours autorisée (fonctionnalités strictement
 * nécessaires) — ne correspond à AUCUN purpose Didomi, jamais interrogée
 * auprès de leur API.
 *
 * Contrôle vendor (ex. Plausible) : si fcpConsentConfig.vendorPlausible est
 * renseigné, « analytics » n'est vrai QUE si le purpose Analytics ET ce
 * vendor sont tous deux autorisés. Vide -> comportement inchangé (purpose
 * seul). Ce contrôle reste centralisé ICI : aucun autre script du plugin
 * n'interroge directement le SDK Didomi (analytics.js utilise uniquement
 * fcpConsent.hasConsent()).
 *
 * Sans configuration (fcpConsentConfig.configured === false) : deny-by-default
 * pour analytics/marketing, aucune tentative de contacter Didomi, aucune erreur.
 */
(function () {
    'use strict';

    if (window.fcpConsent) {
        return; // évite une double initialisation
    }

    var cfg = window.fcpConsentConfig || {};
    var configured = cfg.configured === true;
    var listeners = [];
    var lastState = null;

    function computeState(Didomi) {
        var analytics = false;
        var marketing = false;
        try {
            if (Didomi && typeof Didomi.getUserConsentStatusForPurpose === 'function') {
                analytics = Didomi.getUserConsentStatusForPurpose(cfg.purposeAnalytics || 'analytics') === true;
                marketing = Didomi.getUserConsentStatusForPurpose(cfg.purposeMarketing || 'advertising') === true;

                // Contrôle vendor additif (ex. Plausible) : purpose ET vendor
                // requis quand un vendor est configuré. Vide -> inchangé.
                if (analytics && cfg.vendorPlausible
                    && typeof Didomi.getUserConsentStatusForVendor === 'function') {
                    analytics = Didomi.getUserConsentStatusForVendor(cfg.vendorPlausible) === true;
                }
            }
        } catch (e) {
            analytics = false;
            marketing = false;
        }
        return { functional: true, analytics: analytics, marketing: marketing };
    }

    function notify(state) {
        lastState = state;
        listeners.forEach(function (callback) {
            try { callback(state); } catch (e) { /* un abonné en échec n'affecte pas les autres */ }
        });
    }

    window.fcpConsent = {
        /** @param {'functional'|'analytics'|'marketing'} category */
        hasConsent: function (category) {
            if (category === 'functional') {
                return true;
            }
            if (!configured || !lastState) {
                return false; // deny-by-default : pas de configuration ou état pas encore connu
            }
            return lastState[category] === true;
        },

        /** Enregistre un abonné ; appelé immédiatement si l'état est déjà connu. */
        onChange: function (callback) {
            if (typeof callback !== 'function') {
                return;
            }
            listeners.push(callback);
            if (lastState) {
                callback(lastState);
            }
        },

        /** Réouvre le panneau de préférences Didomi (lien « Gérer mes cookies »). */
        openPreferences: function () {
            if (!configured) {
                return;
            }
            window.didomiOnReady = window.didomiOnReady || [];
            window.didomiOnReady.push(function (Didomi) {
                if (Didomi && Didomi.preferences && typeof Didomi.preferences.show === 'function') {
                    Didomi.preferences.show();
                }
            });
        }
    };

    if (!configured) {
        return; // rien d'autre à faire : aucun SDK à écouter, aucune erreur possible
    }

    window.didomiOnReady = window.didomiOnReady || [];
    window.didomiOnReady.push(function (Didomi) {
        notify(computeState(Didomi));
    });

    window.didomiEventListeners = window.didomiEventListeners || [];
    window.didomiEventListeners.push({
        event: 'consent.changed',
        listener: function () {
            window.didomiOnReady.push(function (Didomi) {
                notify(computeState(Didomi));
            });
        }
    });
})();
