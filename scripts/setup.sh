#!/usr/bin/env bash
# Prépare l'environnement local de développement.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

if [ ! -f .env ]; then
    cp .env.example .env
    echo "Fichier .env créé depuis .env.example — renseignez les valeurs (aucun secret dans Git)."
fi

if command -v composer >/dev/null 2>&1; then
    composer install --no-interaction || echo "composer install optionnel au stade actuel."
else
    echo "Composer absent : les tests PHPUnit nécessiteront son installation."
fi

echo "Setup terminé. Prochaine étape : appliquer les migrations avec scripts/migrate.sh."
