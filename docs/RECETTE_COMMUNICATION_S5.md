# Guide de recette — Communication & Brevo Email (Semaine 5)

Environnement : **staging** uniquement. Aucun secret ne doit apparaître dans le
dépôt, les logs ou le navigateur.

## Pré-requis manuels (côté Brevo) — à votre charge
1. **Clé API Brevo** : Brevo → **SMTP & API → clés API** → créer une clé.
   🔒 À coller **uniquement** dans le `wp-config.php` du staging (jamais ici).
2. **Domaine expéditeur authentifié** : Brevo → **Expéditeurs, domaines** →
   ajouter votre domaine → configurer **SPF/DKIM** (enregistrements DNS chez
   Infomaniak). Sans cela, les e-mails partent mal ou en spam.
3. **Adresse expéditrice** validée (ex. `contact@frenchclassprestige.com`).

## Configuration serveur (staging `wp-config.php`)
Ajouter, avant « That's all » (valeurs réelles, pas de partage) :
```php
define('BREVO_API_KEY', 'VOTRE_CLE_API_BREVO');
define('FCP_MAIL_FROM', 'contact@frenchclassprestige.com');
define('FCP_MAIL_FROM_NAME', 'French Class Prestige');
define('FCP_MAIL_INTERNAL', 'ops@frenchclassprestige.com');
```
`FCP_WHATSAPP_NUMBER` reste `+33656898611` (inchangé).

## Déploiement
1. Remplacer le plugin par **`fcp-horizon-bridge.zip` v0.4.0**.
2. Supabase (Frankfurt) → SQL Editor → exécuter **`005_communication_messages.sql`**.

## Recette fonctionnelle
1. **Envoyer une demande de test** (fenêtre privée, `/demande/`).
2. **Supabase → `communication_messages`** : deux lignes créées :
   - accusé client (recipient = votre e-mail de test),
   - alerte équipe (recipient = `FCP_MAIL_INTERNAL`).
   - Statut : `pending` puis **`sent`** (avec `provider = brevo`,
     `provider_message_id`, `sent_at`) sous ~1 min.
3. **Boîtes mail** : réception de l'accusé client (référence `FCP-2026-…`,
   mention « pas encore une réservation confirmée ») + de l'alerte interne (lien
   fiche Horizon).
4. **Back-office → fiche** : section **Messages transactionnels** — les 2 messages
   avec sens, statut `sent`, horodatage.
5. **Résilience** : temporairement, mettez une `BREVO_API_KEY` invalide → renvoyez
   une demande → les messages passent en `failed` (erreur visible) **mais la
   demande est bien créée**. Rétablissez la clé, cliquez **Relancer** → `sent`.
6. **Règle d'or** : à aucun moment un problème d'e-mail ne doit empêcher la
   création de la demande dans `enquiries`.

## Checklist
```
[ ] 005 exécutée (Success)
[ ] Plugin v0.4.0 actif
[ ] Clé Brevo + expéditeur + SPF/DKIM configurés (hors dépôt)
[ ] Demande test => 2 messages communication_messages
[ ] Statut passe pending -> sent, provider_message_id renseigné
[ ] E-mails reçus (client + interne)
[ ] Fiche : section Messages transactionnels correcte
[ ] Clé invalide => failed MAIS demande créée ; Relancer => sent
[ ] Aucun secret dans logs / navigateur / dépôt
```

## Rollback
- Revenir au plugin précédent (v0.3.0).
- `005` est additive et non destructive ; en staging, on peut vider la table :
  `truncate table public.communication_messages;`
- Retirer les `define(...)` Brevo du `wp-config.php` si besoin.
