#!/bin/bash

# Initial server setup for KTM-WDC
# Run this once on your server

echo "🔧 Setting up KTM-WDC server..."

ssh root@your-server-ip.com << 'ENDSSH'
    # Update system
    apt update && apt upgrade -y
    
    # Install PHP and extensions
    apt install -y php8.3 php8.3-fpm php8.3-cli php8.3-common \
        php8.3-pgsql php8.3-pdo php8.3-mbstring php8.3-xml \
        php8.3-curl php8.3-zip php8.3-gd php8.3-intl
    
    # Install Composer
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
    
    # Install Nginx
    apt install -y nginx
    
    # Install PostgreSQL
    apt install -y postgresql postgresql-contrib
    
    # Create project directory
    mkdir -p /var/www/html/kwdc
    
    # Clone repository
    cd /var/www/html/kwdc
    git clone https://github.com/kirannetpack-ui/ktm-wdc.git .
    
    # Install dependencies
    composer install --no-dev --optimize-autoloader
    
    # Set permissions
    chown -R www-data:www-data /var/www/html/kwdc
    chmod -R 775 storage bootstrap/cache
    
    echo "✅ Server setup complete!"
ENDSSH