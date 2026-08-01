# Changelog — fcp-horizon-bridge

Toutes les évolutions notables du plugin et du socle Horizon.

## [0.7.1] — Semaine 5 (correctif recette — focus programmatique C1/C2)
### Corrigé
- **A4** (recette staging) : le focus automatique vers `#fcp-form-errors`
  (après erreur, C1) et `#fcp-confirmation` (après succès, C2) pouvait être
  ignoré par le navigateur lorsque l'appel `.focus()` intervenait dans la
  même exécution que le retrait de `hidden` (recalcul d'affichage pas encore
  effectué). Le focus est désormais différé d'une frame (`requestAnimationFrame`,
  repli synchrone si indisponible) via un nouvel helper `focusSoon()`.
### Garantie
- Aucune autre modification (`Communication`/Brevo, Didomi/Plausible,
  consentements, logique métier) — 56 tests / 153 assertions, 0 régression.

## [0.7.0] — Semaine 5 (UX, responsive, accessibilité — formulaire)
### Ajouté
- **C1** : `tabindex="-1"` sur `#fcp-form-errors` — le focus programmatique
  déjà appelé dans `form.js` devient réellement effectif après une erreur.
- **C2** : `tabindex="-1"` sur `#fcp-confirmation` + `focus({preventScroll:true})`
  après succès réel, sans double déplacement de défilement.
- **C3** : état d'envoi visible (« Envoi en cours… », `aria-busy`, bouton
  visuellement désactivé), restauré uniquement en cas d'échec.
- **C4** : `scrollIntoView` instantané si `prefers-reduced-motion: reduce`,
  repli sûr si `matchMedia` indisponible.
- `tests/Unit/FormRendererMarkupTest.php` + stubs WP de test dans
  `tests/bootstrap.php` (aucun effet en environnement réel).
- `docs/UX_ACCESSIBILITY_S5.md`, `docs/RECETTE_UX_S5.md` : documentation,
  constat sur les template-parts inertes (décision reportée après RC),
  protocole de mesure C5/C6/C7 (contraste, zones tactiles, zoom 200 %) —
  corrections conditionnelles à un écart mesuré en staging, non anticipées.
### Garantie
- Aucune modification de `Communication`/`Brevo`, `Didomi`/`Plausible`,
  des événements analytics ni des consentements métier Horizon.
- 56 tests / 153 assertions — 0 régression sur les 54 précédents.

## [0.6.0] — Semaine 5 (analytics Plausible, gated par consentement)
### Ajouté
- **`window.fcpAnalytics`** (`track`/`trackOnce`) : file d'attente bornée (20)
  et dédupliquée par clé, mémoire de page uniquement (jamais persistée).
  N'interroge jamais Didomi directement — s'appuie uniquement sur
  `window.fcpConsent`. Utilise le shim officiel Plausible (aucun moteur de
  chargement maison).
- **Injection du script Plausible** strictement post-consentement, unique
  (garde d'état `not_started/loading/loaded/failed`), sans relance automatique
  après échec réseau/bloqueur.
- **`consent.js`** (additif) : `hasConsent('analytics')` exige désormais
  purpose **et** vendor Plausible quand `DIDOMI_VENDOR_PLAUSIBLE` est
  configuré ; comportement inchangé si vide.
- **Instrumentation formulaire** : `enquiry_form_viewed/_started/_submitted/
  _success/_error`, mutuellement exclusifs et scopés par tentative
  (`attemptId`) — un nouvel essai après échec redéclenche `submitted`.
- **Clics de contact** génériques (`tel:`, `mailto:`, `wa.me`) : `phone_clicked`,
  `email_clicked`, `whatsapp_clicked`, sans aucune donnée transmise.
- `Config` : `PLAUSIBLE_SCRIPT_URL`, domaine/URL validés et neutralisés si
  invalides (jamais transmis tels quels au navigateur).
- `docs/ANALYTICS_PLAUSIBLE.md` : configuration, événements, procédure de
  recette, limite connue sur le retrait de consentement.
### Garantie
- Sans `PLAUSIBLE_DOMAIN` ou sans consentement analytics : **aucune requête**
  vers Plausible. Une panne Plausible ne produit **aucune erreur bloquante**
  et n'affecte jamais la création d'une demande Horizon.
- 54 tests / 148 assertions — 0 régression sur les 48 tests précédents.

## [0.5.0] — Semaine 5 (consentement Didomi)
### Ajouté
- **Consentement (Didomi)** : injection du snippet officiel (jamais reconstruit)
  au plus haut de `<head>`, injection unique, aucune sortie sans configuration.
- `ConsentCategory` (Domain, pur) : functional/analytics/marketing ; `functional`
  jamais envoyé à l'API Didomi (garde-fou testé).
- Configuration centralisée sans valeur en dur : `DIDOMI_SDK_EMBED`,
  `DIDOMI_PURPOSE_ANALYTICS`, `DIDOMI_PURPOSE_MARKETING`, `DIDOMI_VENDOR_PLAUSIBLE`.
- Façade `window.fcpConsent` (`hasConsent`, `onChange`, `openPreferences`) —
  s'appuie uniquement sur les points d'intégration officiels Didomi
  (`didomiOnReady`/`didomiEventListeners`), aucun moteur de chargement maison.
- `docs/CONSENT_DIDOMI.md` : configuration console, variables, mécanisme de
  chargement natif à appliquer au lot Plausible, procédure de recette.
### Garantie
- Sans configuration : `functional` = `true`, `analytics`/`marketing` = `false`
  (deny-by-default), aucune bannière, aucune erreur.
- Aucun impact sur la couche Communication/Brevo ni sur les consentements
  métier Horizon (`contacts.consent_marketing` reste séparé).
- 48 tests / 138 assertions — 0 régression sur les 43 tests précédents.

## [0.4.0] — Semaine 5 (couche de communication + Brevo Email)
### Ajouté
- **Couche de communication générique** indépendante du fournisseur : contrats
  `CommunicationProvider` / `EmailProvider` / `WhatsAppProvider` (réservé),
  objets-valeur (`OutboundMessage`, `EmailMessage`, `SendOutcome`), rendu de
  templates FR i18n-ready (`EmailTemplateRegistry`).
- **Outbox résiliente** (`OutboxProcessor`) : file `pending → processing → sent /
  retry_scheduled → failed`, « claim » atomique (pas de double envoi), réessais
  bornés à délai progressif, relance manuelle.
- **Brevo Email** (`BrevoEmailProvider` + `BrevoClient`) : accusé de réception
  client + notification interne équipe, historisés.
- Migration `005_communication_messages.sql` (source de vérité + outbox).
- Back-office : section **Messages transactionnels** dans la fiche (sens, statut,
  horodatages, erreurs, bouton **Relancer**).
- Traitement asynchrone via WP-cron (immédiat après une demande + filet 5 min).
### Garantie
- **Un échec d'e-mail n'empêche ni ne perd jamais une demande Horizon.**
- Le lien `wa.me` vers le numéro public reste inchangé.

## [0.3.0] — Semaine 4 (back-office Horizon)
- Menu Horizon protégé par capacité `fcp_horizon_access`, tableau de bord, liste,
  fiche (client, mission, communications, journal), changement de statut contrôlé
  + note interne. Migration `004_enquiry_notes.sql`.

## [0.2.x] — Semaine 3 (durcissements)
- `/form-token` (jeton anti-cache), `Idempotency-Key`, `/receipt`, compat clés
  Supabase legacy/`sb_secret_`, purge IP 12 mois (`003`), harnais de tests
  d'intégration hors ligne.

## [0.1.0] — Semaines 1–2 (socle + parcours public)
- Plugin, API v1 (`/health`, `/enquiries`, `whatsapp-opened`), formulaire
  intelligent, référence FCP serveur, migrations `001`/`002`, thème enfant Divi.
