# Tests d'intégration

## Harnais hors ligne (actif)

`EnquiryFlowTest` exerce l'**orchestration complète** (service + repositories)
contre un **double Supabase en mémoire** (`FakeSupabase`) qui simule PostgREST :
filtres `eq.`, unicité e-mail (→ 409) et génération de `public_reference` par le
trigger. Aucun Supabase réel, aucune base, aucun Docker → **exécutable en CI**.

Couverture :
1. Soumission complète → `contacts` / `enquiries` / `enquiry_details` /
   `communications` (`prepared`) / `audit_logs` (`enquiry.created`), référence
   `FCP-…`, `whatsapp_url` construite ; **pas de PII** dans le journal.
2. Rapprochement : même e-mail → **1 contact**, N demandes.
3. **Course concurrente** : violation UNIQUE (409) interceptée → relecture du
   contact existant, aucune erreur.
4. `whatsapp-opened` : `prepared` → `opened` (jamais `sent`).

```bash
composer test    # suites « unit » + « integration »
```

## Bout-en-bout réel (manuel, staging)

La validation contre un vrai Supabase se fait par la **recette staging**
(`docs/STAGING_RECETTE.md`) : elle couvre en plus le nonce/cache, le rate
limiting HTTP et le rendu du formulaire — éléments hors du périmètre d'un test
PHP pur.
