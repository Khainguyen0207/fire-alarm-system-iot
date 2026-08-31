#!/usr/bin/env sh
set -eu

if ! command -v docker >/dev/null 2>&1; then
    echo "Docker is required."
    exit 1
fi

docker compose up -d --build
docker compose exec -T app php artisan migrate --seed --force

echo "API available at http://localhost:8000"
