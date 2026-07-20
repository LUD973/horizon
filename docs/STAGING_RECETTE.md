# Recette staging — French Class Prestige / Horizon (Sprint 1)

Guide pas à pas, pensé pour une personne **non développeuse**. Objectif :
mettre en service le parcours de demande sur **staging** et vérifier la chaîne
**WordPress → API → Supabase → WhatsApp**.

> ⚠️ **Sécurité — à lire d'abord.**
> La clé **`SUPABASE_SERVICE_ROLE_KEY`** est un secret majeur. Ne la copiez
> **jamais** dans une capture d'écran, un e-mail, un message, un fichier suivi
> par Git, ni dans une page web. Elle se colle **uniquement** dans le fichier de
> configuration serveur décrit à l'étape 5. Si elle fuite, régénérez-la dans
> Supabase (Settings → API → Reset).

---

## Étape 1 — Accéder à Supabase et retrouver le projet Frankfurt

1. Ouvrez `https://supabase.com` → **Sign in**.
2. Sur le **Dashboard**, la liste des projets s'affiche.
3. Cliquez le projet dont la **Region** indique **Central EU (Frankfurt)**.
   (La région est visible dans **Project Settings → General → Region**.)

☐ *Résultat attendu : vous êtes dans le bon projet, région Frankfurt.*

---

## Étape 2 — Ouvrir le SQL Editor et exécuter les migrations

1. Menu de gauche → **SQL Editor**.
2. Cliquez **+ New query**.
3. Ouvrez, depuis le dépôt, le fichier
   `supabase/migrations/001_initial_schema.sql`, **copiez tout** son contenu,
   collez-le dans l'éditeur, puis cliquez **Run** (ou Ctrl/Cmd + Entrée).
4. Message attendu : **Success. No rows returned**.
5. Répétez avec `supabase/migrations/002_feature_flags_seed.sql`.

☐ *Résultat attendu : les deux requêtes se terminent sans erreur.*

> Les migrations sont **idempotentes** : les relancer ne casse rien.

---

## Étape 3 — Vérifier que les tables sont créées

1. Menu de gauche → **Table Editor**.
2. Vérifiez la présence de : `contacts`, `organizations`, `enquiries`,
   `enquiry_details`, `communications`, `audit_logs`, `feature_flags`.
3. Ouvrez `feature_flags` : trois lignes `concierge_enabled`,
   `membership_enabled`, `ai_enabled`, toutes à **false**.

Vérification facultative (SQL Editor) :

```sql
select name, enabled from public.feature_flags order by name;
```

☐ *Résultat attendu : 7 tables présentes, 3 drapeaux à false.*

---

## Étape 4 — Installer le thème enfant et le plugin sur WordPress staging

Deux méthodes, selon votre confort.

**Méthode A — depuis l'admin WordPress (ZIP)**
1. Préparez deux archives ZIP à partir du dépôt :
   - le dossier `wp-content/themes/fcp-child/` → `fcp-child.zip`
   - le dossier `wp-content/plugins/fcp-horizon-bridge/` → `fcp-horizon-bridge.zip`
2. WordPress **staging** → **Apparence → Thèmes → Ajouter → Téléverser** →
   `fcp-child.zip` → **Installer**.
3. **Extensions → Ajouter → Téléverser** → `fcp-horizon-bridge.zip` → **Installer**.

**Méthode B — par SFTP (Infomaniak)**
1. Connectez-vous en SFTP à l'hébergement **staging**.
2. Copiez `fcp-child/` dans `.../wp-content/themes/`.
3. Copiez `fcp-horizon-bridge/` dans `.../wp-content/plugins/`.

☐ *Résultat attendu : thème et plugin visibles dans WordPress (pas encore activés).*

---

## Étape 5 — Renseigner les secrets (côté serveur uniquement)

**Approche retenue (compatible Infomaniak) : un fichier de secrets hors dépôt,
chargé par `wp-config.php`.** Les secrets ne sont donc ni dans Git, ni exposés
au navigateur.

1. En SFTP, créez un fichier **au-dessus de la racine publique** du site
   (donc **hors** du dossier servi par le web), par exemple
   `.../private/fcp-secrets.php`. S'il n'existe pas de dossier privé, placez-le
   au moins **hors** de `wp-content` et jamais dans le dépôt.
2. Contenu du fichier (remplacez les valeurs entre guillemets par les vôtres —
   **ne me les envoyez jamais**) :

```php
<?php
// Fichier de secrets — HORS dépôt Git, hors racine publique. Ne jamais partager.
define('SUPABASE_URL', 'https://VOTRE-PROJET.supabase.co');
define('SUPABASE_SERVICE_ROLE_KEY', 'COLLEZ_ICI_VOTRE_CLE_SERVICE_ROLE');
define('FCP_ENV', 'staging');
define('FCP_WHATSAPP_NUMBER', '+33XXXXXXXXX');   // numéro professionnel
define('FCP_ALLOWED_ORIGINS', '');                // laisser vide (même origine)
```

3. Protégez le fichier en lecture seule propriétaire (si accès SSH) :

```bash
chmod 600 /chemin/vers/private/fcp-secrets.php
```

4. Dans `wp-config.php`, **avant** la ligne
   `/* That's all, stop editing! */`, ajoutez :

```php
require_once dirname(__DIR__) . '/private/fcp-secrets.php'; // adaptez le chemin
```

> Le plugin lit ces constantes automatiquement (couche `Config`). La clé
> `service_role` reste **exclusivement** côté serveur : elle n'apparaît jamais
> dans une page ni dans le JavaScript.

☐ *Résultat attendu : `wp-config.php` charge le fichier de secrets ; la clé
n'est nulle part dans le site public ni dans Git.*

---

## Étape 6 — Activer le thème et le plugin

1. **Apparence → Thèmes** → activez **FCP — French Class Prestige (enfant Divi)**.
   (Le thème parent **Divi** doit être présent sur le site.)
2. **Extensions** → activez **FCP Horizon Bridge**.

☐ *Résultat attendu : thème enfant actif, plugin actif, aucune erreur.*

---

## Étape 7 — Tester le endpoint santé

Dans un navigateur, ouvrez :

```
https://VOTRE-STAGING/wp-json/fcp/v1/health
```

Réponse attendue (statut **200**) :

```json
{"status":"ok","checks":{"supabase_configured":true,"supabase_reachable":true}}
```

- Si `supabase_reachable` est **false** → vérifiez l'URL et la clé à l'étape 5.

☐ *Résultat attendu : 200 et `supabase_reachable: true`.*

---

## Étape 8 — Créer la page « Demande » avec le formulaire

1. **Pages → Ajouter**.
2. Titre : **Demande**. (Vérifiez que le permalien devient `/demande/`, car la
   navigation pointe vers cette adresse.)
3. Dans le contenu, ajoutez un bloc **Shortcode** (ou **Code court**) et saisissez :

```
[fcp_enquiry_form]
```

4. **Publier**.

☐ *Résultat attendu : la page `/demande/` affiche le formulaire.*

---

## Étape 9 — Premier envoi de test (parcours complet)

1. Ouvrez `/demande/` (idéalement sur mobile, largeur 360–390 px).
2. Remplissez le formulaire, cochez **le consentement de traitement**.
3. Cliquez **Vérifier ma demande** → un **récapitulatif** s'affiche.
4. Cliquez **Confirmer et envoyer** → écran de confirmation avec une
   **référence `FCP-2026-000001`**.
5. Cliquez **Poursuivre sur WhatsApp** → WhatsApp s'ouvre avec le message
   prérempli (ceci est une action volontaire ; rien ne s'ouvre tout seul).
6. Dans Supabase → **Table Editor**, vérifiez :
   - `contacts` : 1 ligne (votre e-mail) ;
   - `enquiries` : 1 ligne, `status = new`, `public_reference` renseignée ;
   - `enquiry_details` : 1 ligne liée ;
   - `communications` : 1 ligne WhatsApp — statut **`opened`** après le clic
     (jamais `sent`) ;
   - `audit_logs` : une ligne `enquiry.created`.

☐ *Résultat attendu : la demande apparaît de bout en bout dans Horizon.*

---

## Rollback staging (si besoin)

Le staging est isolé de la production ; rien n'est irréversible.

1. **Désactiver** le plugin : Extensions → **Désactiver** *FCP Horizon Bridge*.
2. **Revenir** au thème précédent : Apparence → Thèmes → activez l'ancien.
3. **Nettoyer les données de test** (Supabase, SQL Editor) :

```sql
-- Supprime UNIQUEMENT les données de test (staging). Jamais en production.
truncate table public.audit_logs, public.communications,
               public.enquiry_details, public.enquiries,
               public.contacts restart identity cascade;
```

4. **Repartir de zéro sur le schéma** (optionnel, staging seulement) : réexécuter
   `001` puis `002`.
5. **Secrets compromis** : Supabase → Settings → API → **Reset** de la clé
   `service_role`, puis mettez à jour `fcp-secrets.php`.

> Aucune action ci-dessus ne doit être menée sur la production sans votre accord
> explicite.
