#!/bin/bash

# Quick one-click deployment script
# Usage: ./deploy-quick.sh

# Configuration - EDIT THESE
SERVER="root@your-server-ip.com"
PROJECT_PATH="/var/www/html/kwdc"

echo "🚀 Deploying KTM-WDC..."

# Push to GitHub
git add .
git commit -m "Quick deploy $(date '+%Y-%m-%d %H:%M:%S')"
git push origin master

# SSH and deploy
ssh $SERVER << 'ENDSSH'
    cd /var/www/html/kwdc
    
    echo "📦 Pulling code..."
    git pull origin master
    
    echo "📦 Installing dependencies..."
    composer install --no-dev --optimize-autoloader
    
    echo "📊 Running migrations..."
    php artisan migrate --force
    
    echo "🗑️ Clearing cache..."
    php artisan optimize:clear
    
    echo "✅ Done!"
ENDSSH

echo "✅ Deployment complete!"