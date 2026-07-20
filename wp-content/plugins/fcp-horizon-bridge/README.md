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
| GET | `/wp-json/fcp/v1/health` | ✅ Semaine 1 |
| POST | `/wp-json/fcp/v1/enquiries` | ✅ Semaine 2 |
| POST | `/wp-json/fcp/v1/enquiries/{ref}/whatsapp-opened` | ✅ Semaine 2 |
| GET | `/wp-json/fcp/v1/enquiries/{ref}/receipt` | ⏳ Semaine 3 |

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
