# Couche de communication Horizon — Documentation technique

## Objectif
Fournir une couche d'envoi/réception **indépendante du fournisseur**, résiliente
(outbox), historisée, et prête à accueillir de nouveaux canaux/fournisseurs sans
modifier le domaine ni la logique métier. Semaine 5 : **Brevo Email** (accusé
client + alerte équipe). WhatsApp par API : reporté (contrats prévus).

## Architecture

```
Domain\Communication\            (PUR — aucune dépendance fournisseur)
  Channel, Direction*, MessageType        vocabulaire interne
  OutboundMessage (interface)             message sortant générique
  EmailMessage : OutboundMessage          message e-mail (clé de template + variables)
  SendOutcome                             résultat interne (accepted / failed + ids)
  CommunicationProvider (interface)       channel(), name(), send(OutboundMessage)
    ├─ EmailProvider (contrat)            réalisé par BrevoEmailProvider
    └─ WhatsAppProvider (contrat)         RÉSERVÉ (BrevoWhatsApp / MetaCloud futurs)
  EmailTemplateRegistry, RenderedEmail    rendu FR (i18n-ready), pur, testable

Application\Communication\
  ProviderResolver (interface)            providerFor(channel)
  ProviderRegistry : ProviderResolver     construit le provider selon la config
  NotificationService                     intentions métier (accusé client, alerte équipe)
  OutboxProcessor                         traite la file : claim, envoi, réessais bornés

Data\
  MessageRepository                       communication_messages (outbox + historique)
  Providers\Brevo\BrevoClient             transport HTTP (clé via config)
  Providers\Brevo\BrevoEmailProvider      traduit Brevo → SendOutcome interne
```
*(Direction est porté par la colonne SQL ; pas de classe dédiée tant qu'inutile.)*

**Règle d'or** : `EnquiryService` appelle `NotificationService` (intention), qui
écrit des **intentions** dans l'outbox. L'envoi réel est **asynchrone**
(`OutboxProcessor` via WP-cron). Un échec d'envoi ne touche que le message —
**jamais** la demande.

## Flux
1. Demande créée dans Supabase (source de vérité).
2. `NotificationService` met en file 2 messages (`pending`) : accusé client + alerte équipe.
3. Réponse HTTP renvoyée au navigateur (lien wa.me inchangé).
4. `OutboxProcessor` (déclenché immédiatement + toutes les 5 min) : `claim`
   atomique → `BrevoEmailProvider.send()` → `sent` (+ provider_message_id) ou
   `retry_scheduled` (backoff progressif) puis `failed` après 5 tentatives.
5. Back-office : la fiche affiche l'historique + bouton **Relancer** si `failed`.

## Fichiers principaux
- `includes/Domain/Communication/*` — contrats + objets-valeur + templates.
- `includes/Application/Communication/*` — registry, resolver, outbox, notifications.
- `includes/Data/MessageRepository.php`, `includes/Data/Providers/Brevo/*`.
- `includes/Application/EnquiryService.php` (intégration), `includes/Api/RestController.php`
  (déclenchement), `includes/Plugin.php` (cron), `includes/Admin/BackOffice.php` (UI).

## Migrations
- `005_communication_messages.sql` — table source de vérité + outbox (statuts,
  tentatives, horodatages livraison, index, unicité provider_message_id, RLS).
  `communications` (existante) conservée pour le suivi wa.me ; pas de double historique.

## Variables d'environnement (noms uniquement)
`BREVO_API_KEY`, `FCP_MAIL_FROM`, `FCP_MAIL_FROM_NAME`, `FCP_MAIL_INTERNAL`,
`FCP_WHATSAPP_NUMBER` (numéro public unique). Tous en config serveur, jamais dans Git.

## Dépendances
Aucune librairie externe (HTTP via `wp_remote_*`). Service : compte Brevo +
domaine expéditeur authentifié (SPF/DKIM).

## Tests
- Unitaires : `EmailTemplateRegistry` (contenu, ADN, template inconnu).
- Intégration (double Supabase + double provider) : envoi réussi ; réessais
  bornés → failed ; canal non configuré → reste pending ; relance ; **accusés
  mis en file** ; **échec e-mail ne perd jamais la demande**.
- 43 tests / 121 assertions au total, exécutables hors ligne.

## Limites connues
- Statuts `delivered`/`read` non alimentés (nécessitent les webhooks Brevo — lot
  WhatsApp/webhooks). Aujourd'hui : `sent`/`failed` seulement.
- Sans `BREVO_API_KEY`, les messages restent `pending` (envoi dès configuration).
- Envoi asynchrone via WP-cron : la ponctualité dépend du trafic / du déclenchement.

## Évolutions prévues
- Providers WhatsApp (`BrevoWhatsAppProvider`, `MetaCloudWhatsAppProvider`) via le
  contrat `WhatsAppProvider`, sans toucher au domaine.
- Webhooks entrants + statuts `delivered`/`read` + threads + rattachement.
- Consentement par canal/finalité (`consent_channel_grants`) pour le marketing.
