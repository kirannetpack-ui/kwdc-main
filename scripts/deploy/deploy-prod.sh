#!/bin/bash

# Production deployment with backup
BACKUP_DIR="/backups/kwdc"
DATE=$(date '+%Y%m%d_%H%M%S')

echo "🚀 Deploying to PRODUCTION..."

# Create backup
echo "📦 Creating database backup..."
ssh root@your-server.com "pg_dump kwdc_production > $BACKUP_DIR/backup_$DATE.sql"

# Deploy
ssh root@your-server.com << 'ENDSSH'
    cd /var/www/html/kwdc
    git pull origin master
    composer install --no-dev --optimize-autoloader
    php artisan migrate --force
    php artisan optimize:clear
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan queue:restart
    sudo systemctl restart php8.3-fpm
    echo "✅ Production deployment complete!"
ENDSSH