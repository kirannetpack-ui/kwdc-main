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
if [[ "${PHASE_ONE_DEMO:-false}" == "true" && "${SEED_PORTAL_ACCOUNTS:-false}" == "true" ]]; then
    php artisan db:seed --class=PortalAccessSeeder --force
    php artisan db:seed --class=NepaliDemoSeeder --force
fi
php artisan optimize:clear
php artisan app:production-preflight --check-database
php artisan config:cache
php artisan route:cache
php artisan view:cache

if command -v apache2-foreground >/dev/null 2>&1; then
    sed -i -E "s/^Listen [0-9]+$/Listen ${PORT}/" /etc/apache2/ports.conf
    sed -i -E "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf
    exec apache2-foreground
fi

exec php artisan serve --host=0.0.0.0 --port="${PORT}"
