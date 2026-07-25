# Changelog — fcp-horizon-bridge

Toutes les évolutions notables du plugin et du socle Horizon.

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
