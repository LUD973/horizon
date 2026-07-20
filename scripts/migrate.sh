#!/usr/bin/env bash
# Applique les migrations Supabase dans l'ordre numérique.
# Usage : DATABASE_URL="postgres://..." ./scripts/migrate.sh
# La chaîne de connexion vient de l'environnement — jamais du dépôt.
set -euo pipefail

: "${DATABASE_URL:?Définir DATABASE_URL (variable d'environnement, hors dépôt)}"

MIGRATIONS_DIR="$(cd "$(dirname "$0")/../supabase/migrations" && pwd)"

echo "Application des migrations depuis : $MIGRATIONS_DIR"
for file in $(ls -1 "$MIGRATIONS_DIR"/*.sql | sort); do
    echo "→ $(basename "$file")"
    psql "$DATABASE_URL" -v ON_ERROR_STOP=1 -f "$file"
done

echo "Migrations appliquées."
