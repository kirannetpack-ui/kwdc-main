#!/bin/bash

# ============================================
# KTM-WDC Manual Deployment Script
# ============================================

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration - UPDATE THESE VALUES
SERVER_HOST="your-server-ip.com"        # Your server IP or domain
SERVER_USER="root"                       # SSH username (root, ubuntu, etc.)
SERVER_PATH="/var/www/html/kwdc"        # Path to your project on server
DB_NAME="kwdc_production"                # Database name
DB_USER="kwdc_user"                      # Database user
DB_PASSWORD="your_password"              # Database password

# Function to print colored output
print_message() {
    echo -e "${GREEN}[$(date '+%Y-%m-%d %H:%M:%S')]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

print_step() {
    echo -e "${BLUE}▶ $1${NC}"
}

print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️ $1${NC}"
}

# Check if running on local machine
if [ ! -d ".git" ]; then
    print_error "Please run this script from your project root directory!"
    exit 1
fi

# Confirm deployment
echo ""
echo "=========================================="
echo "🚀 KTM-WDC Deployment Script"
echo "=========================================="
echo ""
echo "Server: $SERVER_HOST"
echo "Path: $SERVER_PATH"
echo ""
read -p "Are you sure you want to deploy? (y/n): " -n 1 -r
echo ""
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    print_message "Deployment cancelled."
    exit 1
fi

print_step "Starting deployment..."

# Step 1: Push latest code to GitHub
print_step "Step 1: Pushing latest code to GitHub..."
git add .
git commit -m "Manual deployment $(date '+%Y-%m-%d %H:%M:%S')"
git push origin master

if [ $? -ne 0 ]; then
    print_error "Failed to push code to GitHub"
    exit 1
fi
print_success "Code pushed to GitHub"

# Step 2: SSH into server and deploy
print_step "Step 2: Connecting to server and deploying..."

ssh -T $SERVER_USER@$SERVER_HOST << EOF
    echo "Connected to server successfully!"
    
    # Navigate to project directory
    cd $SERVER_PATH
    echo "📍 Current directory: $(pwd)"
    
    # Pull latest code
    echo "📦 Pulling latest code from GitHub..."
    git pull origin master
    
    # Install/Update Composer dependencies
    echo "📦 Installing Composer dependencies..."
    composer install --no-interaction --optimize-autoloader --no-dev
    
    # Create storage link if not exists
    echo "🔗 Creating storage link..."
    php artisan storage:link || true
    
    # Clear all caches
    echo "🗑️ Clearing caches..."
    php artisan optimize:clear
    
    # Run migrations (handle existing tables)
    echo "📊 Running database migrations..."
    
    # Check if stocks table exists
    TABLE_EXISTS=\$(php artisan tinker --execute="echo Schema::hasTable('stocks');" 2>/dev/null)
    
    if [ "\$TABLE_EXISTS" = "true" ]; then
        echo "⚠️ Stocks table already exists, skipping creation..."
        # Add missing columns if needed
        php artisan migrate --force
    else
        php artisan migrate --force
    fi
    
    # Cache configurations
    echo "🗑️ Caching configurations..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    
    # Set permissions
    echo "🔒 Setting permissions..."
    chmod -R 775 storage bootstrap/cache
    chmod -R 775 public/uploads 2>/dev/null || true
    
    # Restart PHP-FPM (adjust version as needed)
    echo "🔄 Restarting PHP-FPM..."
    sudo systemctl restart php8.3-fpm 2>/dev/null || sudo systemctl restart php8.1-fpm 2>/dev/null || echo "PHP-FPM restart skipped"
    
    echo "✅ Server deployment completed!"
EOF

if [ $? -eq 0 ]; then
    print_success "Deployment completed successfully!"
    
    echo ""
    echo "=========================================="
    echo "🎉 KTM-WDC is now live!"
    echo "🌐 Visit: https://ktm-wdc-master-0mkqr3.laravel.cloud/"
    echo "=========================================="
else
    print_error "Deployment failed!"
    exit 1
