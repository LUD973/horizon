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

    var DRAFT_KEY = 'fcp_enquiry_draft_v1';
    var errorsBox = document.getElementById('fcp-form-errors');
    var summary = document.getElementById('fcp-summary');
    var summaryList = document.getElementById('fcp-summary-list');
    var confirmation = document.getElementById('fcp-confirmation');
    var reviewBtn = document.getElementById('fcp-review-btn');
    var editBtn = document.getElementById('fcp-edit-btn');

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
        errorsBox.focus && errorsBox.focus();
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
        summary.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    editBtn.addEventListener('click', function () {
        summary.hidden = true;
        reviewBtn.hidden = false;
    });

    // --- Envoi ---
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var errs = clientErrors();
        showErrors(errs);
        if (errs.length) { summary.hidden = true; reviewBtn.hidden = false; return; }

        var payload = serialize();
        payload._fcp_nonce = form.querySelector('[name="_fcp_nonce"]').value;
        payload.company_website = ''; // honeypot vide

        var submitBtn = document.getElementById('fcp-submit-btn');
        submitBtn.disabled = true;

        fetch(cfg.enquiriesUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        }).then(function (res) {
            return res.json().then(function (body) { return { status: res.status, body: body }; });
        }).then(function (r) {
            submitBtn.disabled = false;
            if (r.status === 201 && r.body.success) {
                onSuccess(r.body);
            } else if (r.status === 422 && r.body.errors) {
                showErrors(Object.keys(r.body.errors).map(function (k) { return r.body.errors[k]; }));
                summary.hidden = true; reviewBtn.hidden = false;
            } else {
                showErrors([r.body.message || 'Une erreur est survenue. Merci de réessayer.']);
                summary.hidden = true; reviewBtn.hidden = false;
            }
        }).catch(function () {
            submitBtn.disabled = false;
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
                try {
                    fetch(cfg.openedBase + encodeURIComponent(body.reference) + '/whatsapp-opened', {
                        method: 'POST',
                        keepalive: true,
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ _fcp_nonce: form.querySelector('[name="_fcp_nonce"]').value })
                    });
                } catch (e) {}
                // La navigation vers wa.me suit naturellement (href).
            });
        } else {
            waBtn.hidden = true;
        }
        confirmation.hidden = false;
        confirmation.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
})();
