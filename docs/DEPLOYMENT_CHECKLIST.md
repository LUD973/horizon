# Checklist de déploiement — Horizon

Déploiement **staging avant production**. **Aucun déploiement en production
sans accord explicite du responsable de validation.** Aucune modification
directe de la production sans migration tracée.

## Avant tout déploiement
- [ ] Branche à jour, CI verte (lint, tests, migrations, scan secrets).
- [ ] Aucun secret dans le diff (`.env` hors dépôt).
- [ ] Variables d'environnement présentes sur la cible (Supabase, WhatsApp, etc.).

## Base de données (Supabase)
- [ ] Migrations appliquées dans l'ordre (`001_…`, `002_…`).
- [ ] Migrations rejouées sans erreur (idempotence vérifiée quand applicable).
- [ ] Sauvegarde récente disponible avant toute migration en production.

## Staging
- [ ] Thème enfant `fcp-child` activé (parent Divi présent).
- [ ] Plugin `fcp-horizon-bridge` activé.
- [ ] `GET /wp-json/fcp/v1/health` renvoie 200.
- [ ] Tests d'acceptation du sprint passés sur staging.

## Production (sur accord explicite uniquement)
- [ ] Validation formelle reçue.
- [ ] Fenêtre de déploiement confirmée.
- [ ] Smoke test post-déploiement (`/health`, parcours de demande).
- [ ] Procédure de rollback prête (restauration base + version précédente).

## Après déploiement
- [ ] Observabilité : erreurs, santé API, échecs d'envoi surveillés.
- [ ] Journal `audit_logs` alimenté.
