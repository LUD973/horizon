# Horizon — Document de continuité (Sprint 1)

> État de référence pour reprendre le projet sans perte de contexte.
> À mettre à jour après chaque lot dès que le contexte de session dépasse 65 %.

## Repère Git
- **Branche active** : `claude/fcp-execution-phase-bwtydk`
- **Dernier commit (avant ce document)** : `d3e82ae` — feat(consent) lot 1 Didomi
- **Version plugin** : **v0.5.0** (Didomi)
- **État Git** : propre après ce commit de documentation, poussé sur
  `origin/claude/fcp-execution-phase-bwtydk`
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

## Tests
- **48 tests / 138 assertions** (unitaires + intégration hors ligne, double
  Supabase + double provider + Didomi). **0 régression** sur les 43 précédents
  ni sur la couche Communication/Brevo. Exécution : `vendor/bin/phpunit`.

## Documentation déjà créée
- `16_IMPLEMENTATION_PLAN_SOLO.md`, `README.md`, `CHANGELOG.md`
- `docs/DEPLOYMENT_CHECKLIST.md`, `docs/STAGING_RECETTE.md`
- `docs/PV_RECETTE_BACKOFFICE_S4.md`
- `docs/COMMUNICATION_LAYER.md`, `docs/RECETTE_COMMUNICATION_S5.md`
- `docs/CONSENT_DIDOMI.md`
- plugin `README.md`, `tests/Integration/README.md`

## Variables d'environnement (noms uniquement)
`FCP_ENV`, `FCP_DEBUG`, `SUPABASE_URL`, `SUPABASE_SERVICE_ROLE_KEY`,
`FCP_ALLOWED_ORIGINS`, `FCP_RATE_LIMIT_PER_MIN`, `FCP_WHATSAPP_NUMBER`,
`BREVO_API_KEY`, `FCP_MAIL_FROM`, `FCP_MAIL_FROM_NAME`, `FCP_MAIL_INTERNAL`,
`PLAUSIBLE_DOMAIN`, `DIDOMI_NOTICE_ID`, `DIDOMI_SDK_EMBED`,
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

## Reste du Sprint 1 (Semaine 5–6)
- **Semaine 5 (en cours)** : ~~Didomi~~ ✅ livré → **Plausible** (analytics gated),
  puis UX, responsive, accessibilité.
- **Semaine 6** : stabilisation, performance, non-régression, doc d'exploitation,
  checklist, release candidate.

## Prochaine action exacte
**Démarrer Plausible** (analytics) : script chargé **uniquement** si
`fcpConsent.hasConsent('analytics')` est vrai (via le mécanisme natif Didomi
documenté dans `docs/CONSENT_DIDOMI.md` — vérifier l'attribut de blocage exact
à ce moment-là), événements minimum (ouverture formulaire, début de saisie,
demande envoyée, clics téléphone/WhatsApp/e-mail), **aucune donnée personnelle**
transmise à Plausible, aucun événement avant consentement.
