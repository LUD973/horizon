# Horizon — Document de continuité (Sprint 1)

> État de référence pour reprendre le projet sans perte de contexte.
> À mettre à jour après chaque lot dès que le contexte de session dépasse 65 %.

## Repère Git
- **Branche active** : `claude/fcp-execution-phase-bwtydk`
- **Dernier commit** : `0f4b1e8` — fix(ux) v0.7.3 (correctifs recette S5)
- **Version plugin** : **v0.7.3** — **Semaine 5 officiellement close, GO Release Candidate Sprint 1**
- **État Git** : propre, poussé sur `origin/claude/fcp-execution-phase-bwtydk`
- **Tag conseillé** : `v0.7.3-rc1` (non créé automatiquement — à valider explicitement)
- **Mono-dépôt** : `wp-content/themes/fcp-child`, `wp-content/plugins/fcp-horizon-bridge`,
  `supabase/migrations`, `docs/`.

## Environnements
- **Staging** : `staging.frenchclassprestige.com` (Infomaniak, WordPress + Divi),
  séparé de la production. Production non touchée.
- **Supabase** : projet **Frankfurt** « projet staging » (8 tables + `enquiry_notes`
  + `communication_messages`). Clé `service_role` (JWT legacy) via `wp-config.php`.

## Fonctionnalités livrées (Semaines 1 → 4)
- **S1–S2** : plugin, API v1 (`/health`, `/form-token`, `POST /enquiries`,
  `/enquiries/{ref}/receipt`, `/enquiries/{ref}/whatsapp-opened`), formulaire
  intelligent (validation serveur, honeypot, rate limit, nonce anti-cache,
  Idempotency-Key), **référence FCP générée serveur** (trigger), récap + lien
  `wa.me`, thème enfant Divi. Migrations `001`/`002`.
- **S3** : durcissements + purge IP 12 mois (`003`) + harnais de tests hors ligne.
- **S4 — Back-office Horizon** : menu protégé par capacité `fcp_horizon_access`,
  tableau de bord, liste, fiche (client, mission, communications, journal),
  **changement de statut contrôlé** (`EnquiryStatus`) + **note interne**
  (`004_enquiry_notes.sql`). **PV de recette** : `docs/PV_RECETTE_BACKOFFICE_S4.md`
  — recette staging 15/15 validée, **GO Semaine 5**.

## Lot en cours — Communication + Brevo Email (Semaine 5, Option C)
- **Migration** : `005_communication_messages.sql` (source de vérité + outbox ;
  index, unicité `provider_message_id`, RLS). `communications` conservée pour le
  suivi `wa.me` (pas de double historique).
- **Architecture provider-agnostique** :
  `CommunicationProvider` (base) → `EmailProvider` (réalisé) et `WhatsAppProvider`
  (contrat réservé, aucun code mort). Le domaine ignore Brevo.
- **Outbox** (`OutboxProcessor`) : `pending → processing → sent /
  retry_scheduled → failed`, « claim » atomique (pas de double envoi), réessais
  bornés (5) à backoff progressif, **relance manuelle**. WP-cron immédiat + filet 5 min.
- **BrevoEmailProvider** (+ `BrevoClient`) : traduit les réponses Brevo en
  `SendOutcome` interne.
- **Accusé client** : `EmailTemplateRegistry::CLIENT_ACK` (référence, type, date,
  délai, « pas encore une réservation confirmée »).
- **Notification équipe FCP** : `EmailTemplateRegistry::INTERNAL_ALERT` (résumé +
  lien fiche Horizon), destinataire `FCP_MAIL_INTERNAL`.
- **Interface Communications (Horizon)** : fiche → section *Messages
  transactionnels* (sens, statut, horodatages, erreurs, **Relancer**).
- **Règle d'or** : un échec e-mail ne perd JAMAIS une demande (vérifié par test).

## Lot livré — Consentement Didomi (Semaine 5)
- **`ConsentCategory`** (Domain, pur) : `functional` / `analytics` / `marketing` ;
  `functional` toujours autorisé et **jamais** envoyé à l'API Didomi (garde-fou
  testé). Séparation stricte avec les consentements **métier** Horizon
  (`contacts.consent_marketing`), inchangés.
- **Snippet officiel non reconstruit** : `Config::didomiSdkEmbed()` lit
  `DIDOMI_SDK_EMBED` (copié tel quel depuis Console Didomi → Publish) et
  `PublicSite\Consent` l'imprime **verbatim**, une seule fois, en tête de
  `wp_head`. Sans configuration : **aucune sortie**, aucune bannière, aucune erreur.
- **Purposes/vendor centralisés** (sans valeur en dur) : `DIDOMI_PURPOSE_ANALYTICS`
  (défaut `analytics`), `DIDOMI_PURPOSE_MARKETING` (défaut `advertising`),
  `DIDOMI_VENDOR_PLAUSIBLE` (réservé, vide par défaut).
- **Façade `window.fcpConsent`** (`assets/js/consent.js`) : `hasConsent(category)`,
  `onChange(callback)` (multi-abonnés), `openPreferences()` (prêt pour un futur
  lien « Gérer mes cookies »). S'appuie **uniquement** sur les points
  d'intégration officiels Didomi (`didomiOnReady`, `didomiEventListeners`,
  évènement `consent.changed`) — **aucun moteur de chargement maison**.
  Deny-by-default (`analytics`/`marketing` = `false`) tant que non configuré
  ou non consenti.
- **Mécanisme de chargement natif pour Plausible** : documenté dans
  `docs/CONSENT_DIDOMI.md` (blocage automatique par vendor, ou balisage
  `data-purpose`/`data-vendor`) — **attribut `type` exact à vérifier** dans la
  doc Didomi au moment du lot Plausible (non figé, pour éviter tout code deviné).

## Lot livré — Analytics Plausible (Semaine 5)
- **`window.fcpAnalytics`** (`assets/js/analytics.js`) : `track`/`trackOnce`,
  file d'attente **bornée (20)**, **dédupliquée par clé**, **mémoire de page
  uniquement** (jamais persistée). **N'interroge jamais Didomi directement** —
  seule source de vérité : `window.fcpConsent`. Utilise le **shim officiel
  Plausible** (`window.plausible`/`.q`), aucun moteur de chargement maison.
- **Injection du script** : jamais côté PHP (serait pré-consentement) ; créée
  dynamiquement en JS, **uniquement** après `hasConsent('analytics') === true`.
  États explicites `not_started/loading/loaded/failed`, injection unique,
  **aucune relance automatique** après échec réseau/bloqueur (limite documentée).
- **Vendor Plausible dans `consent.js`** (additif, comme prévu au Lot Didomi) :
  `hasConsent('analytics')` exige purpose **et** vendor si
  `DIDOMI_VENDOR_PLAUSIBLE` est configuré ; vide → comportement inchangé.
- **Retrait de consentement après octroi** : file invalidée immédiatement,
  **aucun rejeu** à une ré-acceptation dans la même page (limite assumée :
  le script déjà téléchargé ne peut pas être « déchargé », mais la façade
  empêche tout nouvel appel).
- **Instrumentation formulaire** (`form.js`) : `enquiry_form_viewed/_started`
  (une fois par page) ; `_submitted/_success/_error` **scopés par tentative**
  (`attemptId`) — mutuellement exclusifs, un nouvel essai après échec
  redéclenche légitimement `submitted`.
- **Clics de contact** génériques (`tel:`/`mailto:`/`wa.me`) : `phone_clicked`,
  `email_clicked`, `whatsapp_clicked`, **sans aucune propriété** transmise.
- **`Config`** : `PLAUSIBLE_SCRIPT_URL` + domaine/URL **validés et neutralisés**
  si malformés (jamais transmis tels quels au navigateur).
- Doc : `docs/ANALYTICS_PLAUSIBLE.md`.

## Lot livré — UX / Accessibilité du formulaire (Semaine 5, fin)
- **Périmètre réel** : uniquement `fcp-horizon-bridge` (formulaire `/demande/` +
  confirmation) — seul élément dynamique réellement servi hors Divi.
- **C1** : `tabindex="-1"` sur `#fcp-form-errors` (`FormRenderer.php`) — le
  `.focus()` déjà présent dans `form.js` devient réellement effectif.
- **C2** : `tabindex="-1"` sur `#fcp-confirmation` + `focus({preventScroll:true})`
  après succès réel (pas de double déplacement de défilement).
- **C3** : `setSending()` — texte « Envoi en cours… », `aria-busy`, bouton
  visuellement désactivé ; restauré uniquement en cas d'échec (jamais sur
  succès, le formulaire étant masqué). Logique métier de soumission inchangée.
- **C4** : `revealScrollInto()` — scroll instantané si
  `prefers-reduced-motion: reduce`, repli sûr si `matchMedia` indisponible.
- **`FormRendererMarkupTest`** vérifie les deux `tabindex="-1"` par rendu réel
  (stubs WP de test ajoutés dans `tests/bootstrap.php`, sans effet en
  environnement réel).
- **Constat documenté (aucune action)** : `template-parts/{header,hero,
  intent-selector,footer}.php` et `nav.js` sont **inertes** (jamais inclus par
  le thème) — la Home réelle est construite dans Divi. Décision de câblage/
  archivage/suppression **reportée après la Release Candidate**.
- **C5 (contraste), C6 (zones tactiles), C7 (zoom 200 %)** : **non codés par
  anticipation**. Protocole de mesure détaillé dans `docs/RECETTE_UX_S5.md`
  (tableaux à compléter en staging) ; correction uniquement si écart mesuré
  (< 4,5:1 texte courant / < 3:1 grand texte pour C5).

## Tests
- **56 tests / 153 assertions** (unitaires + intégration hors ligne, double
  Supabase + double provider + Didomi + Plausible + rendu FormRenderer).
  **0 régression** sur les 54 précédents ni sur Communication/Brevo/Didomi/
  Plausible. Exécution : `vendor/bin/phpunit`.

## Documentation déjà créée
- `16_IMPLEMENTATION_PLAN_SOLO.md`, `README.md`, `CHANGELOG.md`
- `docs/DEPLOYMENT_CHECKLIST.md`, `docs/STAGING_RECETTE.md`
- `docs/PV_RECETTE_BACKOFFICE_S4.md`
- `docs/COMMUNICATION_LAYER.md`, `docs/RECETTE_COMMUNICATION_S5.md`
- `docs/CONSENT_DIDOMI.md`
- `docs/ANALYTICS_PLAUSIBLE.md`
- `docs/UX_ACCESSIBILITY_S5.md`, `docs/RECETTE_UX_S5.md`
- plugin `README.md`, `tests/Integration/README.md`

## Variables d'environnement (noms uniquement)
`FCP_ENV`, `FCP_DEBUG`, `SUPABASE_URL`, `SUPABASE_SERVICE_ROLE_KEY`,
`FCP_ALLOWED_ORIGINS`, `FCP_RATE_LIMIT_PER_MIN`, `FCP_WHATSAPP_NUMBER`,
`BREVO_API_KEY`, `FCP_MAIL_FROM`, `FCP_MAIL_FROM_NAME`, `FCP_MAIL_INTERNAL`,
`PLAUSIBLE_DOMAIN`, `PLAUSIBLE_SCRIPT_URL`, `DIDOMI_NOTICE_ID`, `DIDOMI_SDK_EMBED`,
`DIDOMI_PURPOSE_ANALYTICS`, `DIDOMI_PURPOSE_MARKETING`, `DIDOMI_VENDOR_PLAUSIBLE`.
*(Réservé futur : `BREVO_WHATSAPP_SENDER`.)*
Tous en configuration serveur — **jamais dans Git, les logs ou le navigateur**.

## Points de vigilance
- Sans clé Brevo + domaine authentifié (SPF/DKIM) : messages `pending`/`failed`
  (normal). `delivered`/`read` viendront avec les webhooks.
- Brevo WhatsApp = payant (~300 €) → **API WhatsApp reportée** (lot dédié) ;
  contrats prêts (`WhatsAppProvider`, `MetaCloudWhatsAppProvider` futur).
- Le lien **`wa.me` reste sur le numéro public `+33656898611`** (E.164
  `+33656898611`), inchangé ; aucune migration du compte WhatsApp.
- Déploiement staging = action manuelle (upload ZIP + exécuter migrations SQL).
- **C5/C6/C7 (contraste, zones tactiles, zoom)** : mesures à réaliser en
  staging avant de clore le lot UX (`docs/RECETTE_UX_S5.md`).
- **Template-parts inertes** : décision de câblage/archivage/suppression non
  prise — à traiter après la RC, pas avant.

## Contrôles nécessitant encore une action manuelle dans Divi
- Cohérence visuelle de la Home réelle (titres, alt text, contraste des blocs).
- Comportement clavier/mobile du menu de navigation Divi natif.
- Ajout éventuel de liens `tel:`/`mailto:` visibles (détectés automatiquement
  par `analytics.js` dès qu'ils existeront, sans code supplémentaire).
- Apparence du bandeau Didomi (boutons équilibrés, sans dark pattern).
- Mesures C5/C6/C7 elles-mêmes (outil de contraste, DevTools mobile, zoom).

## Clôture officielle — Semaine 5 (01/08/2026)

**Recette staging complète exécutée** (16 points, `docs/RECETTE_UX_S5.md`,
section « Synthèse »). Trois anomalies détectées et corrigées pendant la
recette :
- **A3 (Bloquante)** : staging exécutait une version obsolète (0.3.0) →
  redéployé.
- **A4 (Majeure)** : focus programmatique C1/C2 non fiable (timing) →
  corrigé v0.7.1 (`focusSoon()`), vérifié en conditions réelles.
- **A2 (Majeure)** : récapitulatif désynchronisable + `hidden` neutralisé
  visuellement par le thème sur les `<section>` → corrigé v0.7.2 + v0.7.3,
  vérifié.

Deux réserves **Mineures** restent ouvertes, sans impact bloquant :
- **A5** : configuration Didomi/Plausible absente sur ce staging (action
  serveur, `wp-config.php` — hors code). **Décision (Semaine 6) : reportée
  sciemment** — Didomi (gratuit en petit volume) et Plausible (payant, hors
  version auto-hébergée) représentent un choix commercial, pas un blocage
  technique. Le comportement de repli (deny-by-default, aucune erreur) reste
  la garantie en vigueur tant que la décision n'est pas prise.
- Défilement automatique manquant sur le bouton « Modifier » (cosmétique).

**Décision : ✅ GO Release Candidate Sprint 1 — version v0.7.3.**

## Reste du Sprint 1 (Semaine 5–6)
- **Semaine 5** : ~~Didomi~~ ✅ ~~Plausible~~ ✅ ~~UX/accessibilité (C1–C4)~~ ✅
  ~~Recette staging~~ ✅ — **close**.
- **Semaine 6** : stabilisation, performance, non-régression, documentation
  d'exploitation, restauration config Didomi/Plausible (A5), préparation de
  la Release Candidate finale. Détail : voir plan dédié communiqué en fin de
  session S5.

## Prochaine action exacte
**Semaine 6, A5 reportée** (décision commerciale, cf. ci-dessus) : passer
directement à la stabilisation / vérification performance / documentation
d'exploitation, avant la Release Candidate finale du Sprint 1.
