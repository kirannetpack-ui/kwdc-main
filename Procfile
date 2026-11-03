web: bash scripts/deploy/start-production.sh
worker: php artisan queue:work --sleep=3 --tries=3 --timeout=120
scheduler: php artisan schedule:work
