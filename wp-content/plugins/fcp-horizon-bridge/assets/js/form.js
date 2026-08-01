/**
 * Formulaire de demande FCP — présentation/UX uniquement.
 * La validation fait autorité côté serveur ; ce script assiste l'utilisateur.
 *
 * Séquence stricte : la demande est enregistrée par le serveur AVANT tout
 * accès à WhatsApp. Le lien wa.me n'apparaît qu'après un succès serveur.
 */
(function () {
    'use strict';

    var cfg = window.fcpEnquiry || {};
    var form = document.getElementById('fcp-enquiry-form');
    if (!form) { return; }

    // --- Analytics (Plausible, gated par consentement) ---
    // Défensif : jamais bloquant pour le parcours de demande, même si
    // fcpAnalytics est absent (script non chargé, bloqueur, etc.).
    function fcpTrack(key, name, properties) {
        try {
            if (window.fcpAnalytics) {
                if (key) {
                    window.fcpAnalytics.trackOnce(key, name, properties || {});
                } else {
                    window.fcpAnalytics.track(name, properties || {});
                }
            }
        } catch (e) { /* jamais bloquant pour la demande */ }
    }

    fcpTrack('enquiry_form_viewed', 'enquiry_form_viewed', { service_category: 'mobility' });

    var startedTracked = false;
    var attemptId = 0; // incrémenté à chaque tentative réelle d'envoi (retry autorisé après échec)

    var DRAFT_KEY = 'fcp_enquiry_draft_v1';
    var errorsBox = document.getElementById('fcp-form-errors');
    var summary = document.getElementById('fcp-summary');
    var summaryList = document.getElementById('fcp-summary-list');
    var confirmation = document.getElementById('fcp-confirmation');
    var reviewBtn = document.getElementById('fcp-review-btn');
    var editBtn = document.getElementById('fcp-edit-btn');
    var submitBtn = document.getElementById('fcp-submit-btn');

    // --- Réduction des mouvements (C4) : scroll instantané si demandé par
    // l'OS/le navigateur, avec repli sûr si matchMedia est indisponible. ---
    function prefersReducedMotion() {
        try {
            return !!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);
        } catch (e) {
            return false; // repli : comportement fluide par défaut
        }
    }
    function revealScrollInto(el) {
        el.scrollIntoView({ behavior: prefersReducedMotion() ? 'auto' : 'smooth', block: 'start' });
    }

    // --- Focus programmatique différé (C1/C2) : un appel .focus() juste après
    // avoir retiré `hidden` peut être ignoré par le navigateur tant que le
    // recalcul d'affichage n'a pas eu lieu. On laisse passer une frame avant
    // de focaliser, pour cibler l'élément une fois réellement visible. ---
    function focusSoon(el, opts) {
        var run = function () { el.focus(opts); };
        if (window.requestAnimationFrame) { window.requestAnimationFrame(run); } else { run(); }
    }

    // --- État d'envoi (C3) : texte temporaire + aria-busy, restauré en cas
    // d'échec uniquement (le formulaire est masqué après un succès réel). ---
    var submitBtnDefaultText = null;
    function setSending(isSending) {
        if (submitBtnDefaultText === null) { submitBtnDefaultText = submitBtn.textContent; }
        submitBtn.disabled = isSending;
        form.setAttribute('aria-busy', isSending ? 'true' : 'false');
        submitBtn.textContent = isSending ? 'Envoi en cours…' : submitBtnDefaultText;
    }

    var LABELS = {
        first_name: 'Prénom', last_name: 'Nom', email: 'E-mail', phone: 'Téléphone',
        organization_name: 'Société', service_date: 'Date', service_time: 'Heure',
        origin: 'Départ', destination: 'Destination', passengers: 'Passagers',
        luggage: 'Bagages', flight_number: 'Vol', train_number: 'Train',
        preferred_channel: 'Canal préféré', message: 'Message'
    };

    // --- Profil : afficher le champ société si « company ». ---
    function syncProfile() {
        var isCompany = form.querySelector('input[name="profile"]:checked').value === 'company';
        var block = form.querySelector('[data-when-company]');
        if (block) { block.hidden = !isCompany; }
    }
    form.querySelectorAll('input[name="profile"]').forEach(function (el) {
        el.addEventListener('change', syncProfile);
    });

    // --- Brouillon local ---
    function serialize() {
        var data = {};
        form.querySelectorAll('input, select, textarea').forEach(function (el) {
            if (!el.name || el.name === '_fcp_nonce' || el.name === 'company_website') { return; }
            if (el.type === 'checkbox') { data[el.name] = el.checked ? '1' : ''; }
            else if (el.type === 'radio') { if (el.checked) { data[el.name] = el.value; } }
            else { data[el.name] = el.value; }
        });
        return data;
    }

    function saveDraft() {
        try { localStorage.setItem(DRAFT_KEY, JSON.stringify(serialize())); } catch (e) {}
    }

    function restoreDraft() {
        try {
            var raw = localStorage.getItem(DRAFT_KEY);
            if (!raw) { return; }
            var data = JSON.parse(raw);
            Object.keys(data).forEach(function (name) {
                var el = form.querySelector('[name="' + name + '"]');
                if (!el) { return; }
                if (el.type === 'checkbox') { el.checked = data[name] === '1'; }
                else if (el.type === 'radio') {
                    var r = form.querySelector('[name="' + name + '"][value="' + data[name] + '"]');
                    if (r) { r.checked = true; }
                } else { el.value = data[name]; }
            });
            syncProfile();
        } catch (e) {}
    }

    form.addEventListener('input', saveDraft);
    // « started » : une seule fois, à la première interaction RÉELLE. Ne se
    // déclenche pas pour restoreDraft() ci-dessous (assignation .value par
    // script ne déclenche pas d'événement input natif).
    form.addEventListener('input', function () {
        if (startedTracked) { return; }
        startedTracked = true;
        fcpTrack('enquiry_form_started', 'enquiry_form_started', { service_category: 'mobility' });
    });
    restoreDraft();
    syncProfile();

    // --- Validation client (miroir léger du serveur) ---
    function clientErrors() {
        var d = serialize();
        var errors = [];
        if (!d.first_name || d.first_name.length < 2) { errors.push('Prénom requis.'); }
        if (!d.last_name || d.last_name.length < 2) { errors.push('Nom requis.'); }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(d.email || '')) { errors.push('E-mail invalide.'); }
        if ((d.phone || '').replace(/\D/g, '').length < 8) { errors.push('Téléphone invalide.'); }
        if (!d.service_date) { errors.push('Date requise.'); }
        else {
            var today = new Date(); today.setHours(0, 0, 0, 0);
            if (new Date(d.service_date) < today) { errors.push('La date doit être aujourd’hui ou ultérieure.'); }
        }
        if (!d.origin) { errors.push('Départ requis.'); }
        if (!d.destination) { errors.push('Destination requise.'); }
        if (!d.passengers || parseInt(d.passengers, 10) < 1) { errors.push('Au moins un passager.'); }
        if (d.profile === 'company' && !d.organization_name) { errors.push('Nom de la société requis.'); }
        if (d.consent_processing !== '1') { errors.push('Le consentement au traitement est requis.'); }
        return errors;
    }

    function showErrors(list) {
        if (!list.length) { errorsBox.hidden = true; errorsBox.innerHTML = ''; return; }
        errorsBox.hidden = false;
        errorsBox.innerHTML = '<ul>' + list.map(function (e) {
            return '<li>' + e.replace(/</g, '&lt;') + '</li>';
        }).join('') + '</ul>';
        focusSoon(errorsBox);
    }

    // --- Récapitulatif ---
    reviewBtn.addEventListener('click', function () {
        var errs = clientErrors();
        showErrors(errs);
        if (errs.length) { return; }

        var d = serialize();
        summaryList.innerHTML = '';
        Object.keys(LABELS).forEach(function (key) {
            if (!d[key]) { return; }
            var dt = document.createElement('dt'); dt.textContent = LABELS[key];
            var dd = document.createElement('dd'); dd.textContent = d[key];
            summaryList.appendChild(dt); summaryList.appendChild(dd);
        });
        summary.hidden = false;
        reviewBtn.hidden = true;
        revealScrollInto(summary);
    });

    editBtn.addEventListener('click', function () {
        summary.hidden = true;
        reviewBtn.hidden = false;
    });

    // Clé d'idempotence : stable pour une même soumission logique (réutilisée
    // si l'utilisateur réessaie après une erreur réseau) — évite les doublons.
    var idempotencyKey = null;
    function uuid() {
        if (window.crypto && window.crypto.randomUUID) { return window.crypto.randomUUID(); }
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            var r = (Math.random() * 16) | 0, v = c === 'x' ? r : (r & 0x3) | 0x8;
            return v.toString(16);
        });
    }

    // Récupère un jeton FRAIS via REST (robuste au cache et à l'état connecté).
    function freshToken() {
        return fetch(cfg.tokenUrl, { headers: { 'Accept': 'application/json' }, cache: 'no-store' })
            .then(function (res) { return res.json(); })
            .then(function (body) { return (body && body.token) || form.querySelector('[name="_fcp_nonce"]').value; })
            .catch(function () { return form.querySelector('[name="_fcp_nonce"]').value; });
    }

    // --- Envoi ---
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var errs = clientErrors();
        showErrors(errs);
        if (errs.length) { summary.hidden = true; reviewBtn.hidden = false; return; }

        setSending(true);
        if (!idempotencyKey) { idempotencyKey = uuid(); }

        // Nouvelle tentative réelle : identifiant dédié (permet un nouveau
        // « submitted » après un échec, sans jamais dupliquer CETTE tentative).
        attemptId += 1;
        var currentAttempt = attemptId;
        fcpTrack('enquiry_form_submitted:' + currentAttempt, 'enquiry_form_submitted', { service_category: 'mobility' });

        var trackedChannel = '';

        freshToken().then(function (token) {
            var payload = serialize();
            payload._fcp_nonce = token;
            payload.company_website = ''; // honeypot vide
            trackedChannel = payload.preferred_channel || '';

            return fetch(cfg.enquiriesUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Idempotency-Key': idempotencyKey
                },
                body: JSON.stringify(payload)
            });
        }).then(function (res) {
            return res.json().then(function (body) { return { status: res.status, body: body }; });
        }).then(function (r) {
            if ((r.status === 201 || r.status === 200) && r.body.success) {
                // Succès réel : le formulaire est masqué juste après (onSuccess) —
                // aucune restauration inutile de l'état d'envoi sur un bouton caché.
                idempotencyKey = null; // succès : on repart neuf pour une éventuelle autre demande
                fcpTrack('enquiry_form_success:' + currentAttempt, 'enquiry_form_success', {
                    service_category: 'mobility',
                    contact_channel: trackedChannel
                });
                onSuccess(r.body);
            } else if (r.status === 422 && r.body.errors) {
                setSending(false);
                fcpTrack('enquiry_form_error:' + currentAttempt, 'enquiry_form_error');
                showErrors(Object.keys(r.body.errors).map(function (k) { return r.body.errors[k]; }));
                summary.hidden = true; reviewBtn.hidden = false;
            } else {
                setSending(false);
                fcpTrack('enquiry_form_error:' + currentAttempt, 'enquiry_form_error');
                showErrors([r.body.message || 'Une erreur est survenue. Merci de réessayer.']);
                summary.hidden = true; reviewBtn.hidden = false;
            }
        }).catch(function () {
            setSending(false);
            fcpTrack('enquiry_form_error:' + currentAttempt, 'enquiry_form_error');
            showErrors(['Connexion impossible. Merci de réessayer.']);
        });
    });

    function onSuccess(body) {
        try { localStorage.removeItem(DRAFT_KEY); } catch (e) {}
        form.hidden = true;
        document.getElementById('fcp-ref').textContent = body.reference;
        document.getElementById('fcp-confirmation-msg').textContent = body.message || '';

        var waBtn = document.getElementById('fcp-whatsapp-btn');
        if (body.whatsapp_url) {
            waBtn.href = body.whatsapp_url;
            waBtn.hidden = false;
            waBtn.addEventListener('click', function () {
                // Trace « opened » (jamais « sent ») avant l'ouverture de WhatsApp.
                // Jeton frais pour rester robuste au cache / à l'état connecté.
                try {
                    freshToken().then(function (token) {
                        fetch(cfg.openedBase + encodeURIComponent(body.reference) + '/whatsapp-opened', {
                            method: 'POST',
                            keepalive: true,
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ _fcp_nonce: token })
                        });
                    });
                } catch (e) {}
                // La navigation vers wa.me suit naturellement (href).
            });
        } else {
            waBtn.hidden = true;
        }
        confirmation.hidden = false;
        revealScrollInto(confirmation);
        // Focus programmatique (C2) : preventScroll évite un second saut de
        // défilement redondant avec revealScrollInto ci-dessus (pas de double
        // déplacement). Annonce correcte via aria-live="polite" déjà en place.
        focusSoon(confirmation, { preventScroll: true });
    }
})();
