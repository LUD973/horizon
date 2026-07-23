# fcp-horizon-bridge

Pont entre le site public French Class Prestige (WordPress / Divi) et
**Horizon Core** (Supabase). Toute la logique métier vit ici — **aucune logique
métier dans Divi**.

## Architecture (séparation stricte)

```
includes/
├─ Support/     Configuration (env), journalisation sobre, drapeaux
├─ Data/        Accès aux données (client Supabase, repositories)
├─ Domain/      Logique métier (référence FCP, règles, validation)
├─ Api/         API REST versionnée « fcp/v1 » (présentation d'API)
├─ PublicSite/  Présentation publique (formulaire, récapitulatif) — S2
└─ Admin/       Back-office Horizon (lecture) — S4
```

- **Présentation** (`PublicSite/`, `Admin/`) ⟶ **Logique** (`Domain/`) ⟶ **Données** (`Data/`).
- La présentation n'accède jamais directement à Supabase.

## Configuration

Variables d'environnement uniquement (voir `.env.example` à la racine du dépôt).
Aucun secret dans le dépôt ni côté navigateur. La clé `service_role` est
utilisée exclusivement côté serveur.

## API v1 (état Sprint 1)

| Méthode | Route | État |
|---|---|---|
| GET | `/wp-json/fcp/v1/health` | ✅ S1 |
| GET | `/wp-json/fcp/v1/form-token` | ✅ S3 (jeton frais, anti-cache) |
| POST | `/wp-json/fcp/v1/enquiries` | ✅ S2 (Idempotency-Key: S3) |
| GET | `/wp-json/fcp/v1/enquiries/{ref}/receipt` | ✅ S3 (données non sensibles) |
| POST | `/wp-json/fcp/v1/enquiries/{ref}/whatsapp-opened` | ✅ S2 |

**Durcissements S3** : jeton (nonce) récupéré via `/form-token` (robuste au cache
et à l'état connecté) ; en-tête `Idempotency-Key` sur `POST /enquiries` (anti-doublon
sur retries) ; compat clés Supabase legacy `service_role` (JWT) **et** nouvelles
`sb_secret_…` ; purge des IP `audit_logs` à 12 mois (tâche quotidienne +
`select public.purge_audit_ip();`).

### Vérifier /health

```bash
curl -s https://<staging>/wp-json/fcp/v1/health | jq
```

Réponse `200` si Supabase est configuré et joignable, `503` sinon.

### Formulaire public

Le formulaire s'intègre dans une page via le shortcode `[fcp_enquiry_form]`
(aucune logique dans Divi). Séquence : validation serveur → enregistrement
(contact → enquiry → details → audit_logs → communications `prepared`) →
référence FCP (serveur) → écran de confirmation → lien wa.me. L'ouverture du
lien marque la communication `opened`, **jamais** `sent`.

## Tests

```bash
composer install
composer test          # PHPUnit : couverture ciblée ~70 % sur Domain/
```
