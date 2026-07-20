# Tests d'intégration

Les tests d'intégration du parcours **formulaire → Supabase** nécessitent un
environnement d'exécution avec :

- une instance WordPress de test (fonctions `wp_remote_*`, transients, nonces) ;
- un projet Supabase de **test** (jamais la production, aucune donnée réelle) ;
- les variables d'environnement Supabase renseignées.

Portée cible (Semaine 3, essentiels dès la Semaine 2) :

1. `POST /fcp/v1/enquiries` avec charge valide → 201, référence `FCP-AAAA-000000`
   retournée, lignes créées dans `contacts`, `enquiries`, `enquiry_details`,
   `communications` (`prepared`) et `audit_logs` (`enquiry.created`).
2. Rapprochement de contact : deux demandes avec le même e-mail → un seul
   `contacts`, deux `enquiries`.
3. Unicité de la référence sous soumissions successives.
4. Échec d'enregistrement simulé → **aucune** `whatsapp_url` renvoyée.
5. `POST /enquiries/{ref}/whatsapp-opened` → communication passe à `opened`
   (jamais `sent`).
6. Nonce absent/invalide → 403 ; honeypot rempli → 400 ; > 5 req/min → 429.

Ces tests seront branchés sur `wp-phpunit` (suite « integration » de
`phpunit.xml.dist`). La logique métier pure est déjà couverte par les tests
unitaires (suite « unit »).
