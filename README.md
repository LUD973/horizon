# Horizon — French Class Prestige

Mono-dépôt du MVP : site public premium (WordPress + Divi) et socle **Horizon
Core** (Supabase). Une seule personne, full-stack, cible 5–6 semaines.

> French Class Prestige est une **Maison française de mobilité et de services
> premium**. La technologie reste discrète ; le vocabulaire public suit l'ADN
> de la Maison.

## Structure

```
horizon/
├─ wp-content/
│  ├─ themes/fcp-child/                 Thème enfant Divi — présentation uniquement
│  └─ plugins/fcp-horizon-bridge/       Logique métier, API v1, formulaire, back-office
├─ supabase/migrations/                 Migrations numérotées (001_, 002_, …)
├─ scripts/                             setup.sh, migrate.sh
├─ docs/                                Checklist de déploiement, exploitation
├─ .github/workflows/ci.yml             CI légère (lint, tests, migrations, secrets)
├─ .env.example                         Modèle de configuration (aucun secret réel)
└─ 16_IMPLEMENTATION_PLAN_SOLO.md       Plan d'exécution solo (source de vérité)
```

La séparation présentation / logique / données est portée par l'arborescence du
plugin (`Support` / `Data` / `Domain` / `Api` / `PublicSite` / `Admin`).
Le mono-dépôt reste factorisable en plusieurs dépôts sans refonte métier.

## Démarrage local

```bash
./scripts/setup.sh                 # crée .env, installe les dépendances
# renseigner .env (Supabase, WhatsApp, …)
DATABASE_URL="postgres://…" ./scripts/migrate.sh
```

Puis, dans WordPress : activer le thème enfant `fcp-child` et le plugin
`fcp-horizon-bridge`, et vérifier :

```bash
curl -s https://<staging>/wp-json/fcp/v1/health
```

## Environnements

`local` → `staging` → `production`. **Staging séparé de la production.**
Aucun déploiement en production sans accord explicite. Voir
`docs/DEPLOYMENT_CHECKLIST.md`.

## Règles

- Git obligatoire, commits courts, jamais de code direct sur `main`.
- Secrets en variables d'environnement, jamais dans le dépôt.
- Aucun mot interdit dans la communication publique (ADN).
- Feature flags : `concierge_enabled`, `membership_enabled`, `ai_enabled` = `false`.
