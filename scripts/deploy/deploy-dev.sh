#!/bin/bash

# Development deployment
SERVER="dev-server.com"
USER="dev-user"
PATH="/home/dev/kwdc"

echo "🚀 Deploying to DEVELOPMENT server..."

ssh $USER@$SERVER << EOF
    cd $PATH
    git pull origin develop
    composer install
    php artisan migrate:fresh --seed
    php artisan optimize:clear
    echo "✅ Development deployment complete!"
EOF