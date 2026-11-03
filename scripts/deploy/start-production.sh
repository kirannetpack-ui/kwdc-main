#!/usr/bin/env bash
set -euo pipefail

if [ -z "${APP_KEY:-}" ]; then
    echo "APP_KEY must be configured as a persistent production secret before startup." >&2
    exit 1
fi

export PORT="${PORT:-8080}"

mkdir -p \
    storage/framework/sessions \
    storage/framework/views \
    storage/framework/cache \
    bootstrap/cache

chmod -R ug+rwX storage bootstrap/cache || true

php artisan storage:link || true
php artisan migrate --force
php artisan db:seed --class=PortalAccessSeeder --force
php artisan db:seed --class=NepaliDemoSeeder --force
php artisan optimize:clear
php artisan app:production-preflight
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec php artisan serve --host=0.0.0.0 --port="${PORT}"
