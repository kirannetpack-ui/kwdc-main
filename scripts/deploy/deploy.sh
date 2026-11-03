#!/bin/bash

# ============================================
# KTM-WDC Deployment Script
# ============================================

echo "=========================================="
echo "🚀 Starting KTM-WDC Deployment"
echo "=========================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Set variables
DEPLOYMENT_DATE=$(date '+%Y-%m-%d %H:%M:%S')
LOG_FILE="storage/logs/deploy_$(date '+%Y%m%d_%H%M%S').log"

# Function to log messages
log_message() {
    echo -e "${GREEN}[$(date '+%Y-%m-%d %H:%M:%S')]${NC} $1"
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" >> $LOG_FILE
}

# Function to handle errors
error_message() {
    echo -e "${RED}[ERROR]${NC} $1"
    echo "[ERROR] $1" >> $LOG_FILE
    exit 1
}

# Step 1: Pull latest code
log_message "📦 Pulling latest code from repository..."
git pull origin master || error_message "Failed to pull code"

# Step 2: Install/Update Composer dependencies
log_message "📦 Installing/Updating Composer dependencies..."
composer install --no-interaction --optimize-autoloader --no-dev || error_message "Composer install failed"

# Step 3: Clear all caches
log_message "🗑️  Clearing all caches..."
php artisan optimize:clear || error_message "Failed to clear caches"

# Step 4: Run migrations (handle existing tables)
log_message "📊 Running database migrations..."
log_message "Checking if tables exist..."

# Run migrations, capture output
MIGRATE_OUTPUT=$(php artisan migrate --force 2>&1)
MIGRATE_EXIT_CODE=$?

if [ $MIGRATE_EXIT_CODE -ne 0 ]; then
    if echo "$MIGRATE_OUTPUT" | grep -q "Duplicate table\|already exists"; then
        log_message "${YELLOW}⚠️  Some tables already exist, skipping duplicate migrations...${NC}"
        
        # Get the current migration batch
        CURRENT_BATCH=$(php artisan migrate:status | grep -oP 'Batch:\s+\K\d+' | head -1)
        if [ -z "$CURRENT_BATCH" ]; then
            CURRENT_BATCH=1
        fi
        
        log_message "Current migration batch: $CURRENT_BATCH"
        log_message "Continuing with remaining migrations..."
        
        # Run remaining migrations
        php artisan migrate --force || error_message "Failed to run remaining migrations"
    else
        error_message "Migration failed: $MIGRATE_OUTPUT"
    fi
fi

log_message "✅ Migrations completed successfully"

# Step 5: Check if stocks table has required columns
log_message "🔧 Verifying stocks table structure..."
php artisan tinker --execute="
try {
    \$hasColumns = Schema::hasColumn('stocks', 'number_of_boxes') && 
                   Schema::hasColumn('stocks', 'batch_id') && 
                   Schema::hasColumn('stocks', 'qr_code_path');
    if (!\$hasColumns) {
        echo 'Missing columns detected. Running add-missing-columns migration...';
        Artisan::call('migrate', ['--path' => 'database/migrations/2026_06_05_000020_add_missing_columns_to_stocks_table.php', '--force' => true]);
        echo Artisan::output();
    }
} catch (Exception \$e) {
    echo 'Table check skipped: ' . \$e->getMessage();
}
" || log_message "${YELLOW}⚠️  Column verification skipped${NC}"

# Step 6: Create storage link
log_message "🔗 Creating storage link..."
php artisan storage:link || log_message "${YELLOW}⚠️  Storage link already exists${NC}"

# Step 7: Clear and cache configurations
log_message "🗑️  Clearing and caching configurations..."
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Step 8: Set permissions (for Linux servers)
if [[ "$OSTYPE" == "linux-gnu"* ]]; then
    log_message "🔒 Setting correct permissions..."
    chmod -R 775 storage bootstrap/cache
    chmod -R 775 public/uploads
    chown -R www-data:www-data storage bootstrap/cache public/uploads 2>/dev/null || true
fi

# Step 9: Restart queue workers (if using queues)
log_message "🔄 Restarting queue workers..."
php artisan queue:restart || log_message "${YELLOW}⚠️  Queue restart skipped (not configured)${NC}"

# Step 10: Run any pending seeders (optional - comment if not needed)
# log_message "🌱 Running database seeders..."
# php artisan db:seed --force || log_message "Seeders skipped"

# Step 11: Deployment summary
log_message "=========================================="
log_message "✅ DEPLOYMENT COMPLETED SUCCESSFULLY!"
log_message "📅 Deployed on: $DEPLOYMENT_DATE"
log_message "📄 Log saved to: $LOG_FILE"
log_message "=========================================="

echo ""
echo "🎉 KTM-WDC is now live!"
echo "🌐 Visit: ktm-wdc-master-0mkqr3.laravel.cloud"
echo ""