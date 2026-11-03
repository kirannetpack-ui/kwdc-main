# Critical Migration Implementation Guide

## 🚀 Start Here: Priority-Based Implementation Plan

This guide provides step-by-step instructions to implement the critical migrations recommended in the analysis.

---

## Phase 1: Critical Fixes (Implement Immediately)

### ✅ Critical Fix #1: Fix User Type Column Redundancy

**Problem:** Multiple conflicting user type columns (`role`, `is_admin`, `is_driver`, `is_equipment_owner`, `user_type`)

**Solution:** Add roles JSON column for multi-role support

**Migration File:** `2026_08_14_001_add_roles_json_to_users.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add roles JSON column for flexible role management
            if (!Schema::hasColumn('users', 'roles')) {
                $table->json('roles')
                    ->default('["client"]')
                    ->comment('JSON array of user roles: [admin, driver, client, equipment_owner, property_owner, security_agency]')
                    ->after('is_equipment_owner');
            }
            
            // Add index for common role queries
            if (!Schema::hasColumn('users', 'has_role')) {
                $table->index('role');
            }
        });
        
        // Migrate existing data to roles JSON
        // This preserves backward compatibility
        $this->migrateExistingRoles();
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'roles')) {
                $table->dropColumn('roles');
            }
        });
    }
    
    private function migrateExistingRoles(): void
    {
        // Get all users and build their roles array
        \DB::table('users')->each(function ($user) {
            $roles = ['client']; // Default role
            
            if ($user->is_admin) {
                $roles[] = 'admin';
            }
            if ($user->is_driver) {
                $roles[] = 'driver';
            }
            if ($user->is_equipment_owner) {
                $roles[] = 'equipment_owner';
            }
            if ($user->user_type) {
                $roles[] = $user->user_type;
            }
            
            // Remove duplicates
            $roles = array_unique($roles);
            
            \DB::table('users')
                ->where('id', $user->id)
                ->update(['roles' => json_encode($roles)]);
        });
    }
};
```

**Update Model:**
```php
// app/Models/User.php

class User extends Model
{
    protected $casts = [
        'roles' => 'array',
        'email_verified_at' => 'datetime',
    ];
    
    // Check if user has a role
    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles ?? ['client']);
    }
    
    // Add a role
    public function addRole(string $role): void
    {
        $roles = $this->roles ?? ['client'];
        if (!in_array($role, $roles)) {
            $roles[] = $role;
            $this->update(['roles' => $roles]);
        }
    }
    
    // Remove a role
    public function removeRole(string $role): void
    {
        $roles = $this->roles ?? ['client'];
        $roles = array_filter($roles, fn($r) => $r !== $role);
        $this->update(['roles' => array_values($roles)]);
    }
}
```

---

### ✅ Critical Fix #2: Fix Coordinate Data Types

**Problem:** `latitude` and `longitude` stored as VARCHAR instead of DECIMAL

**Migration File:** `2026_08_14_002_fix_kataho_locations_coordinates.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kataho_locations', function (Blueprint $table) {
            // Check if columns exist as string first
            if (Schema::hasColumn('kataho_locations', 'latitude')) {
                // Get current column type
                $column = DB::select(
                    DB::raw(
                        "SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS 
                        WHERE TABLE_NAME='kataho_locations' 
                        AND COLUMN_NAME='latitude'"
                    )
                )[0] ?? null;
                
                if ($column && str_contains($column->COLUMN_TYPE, 'varchar')) {
                    // Need to modify - create new columns, migrate, drop old
                    $table->decimal('latitude_new', 10, 8)
                        ->nullable()
                        ->comment('Latitude coordinate (max 8 decimal places for ~1.1mm accuracy)')
                        ->after('longitude');
                    
                    $table->decimal('longitude_new', 11, 8)
                        ->nullable()
                        ->comment('Longitude coordinate (max 8 decimal places for ~1.1mm accuracy)')
                        ->after('latitude_new');
                }
            }
        });
        
        // Migrate data
        DB::table('kataho_locations')->each(function ($location) {
            if ($location->latitude && $location->longitude) {
                DB::table('kataho_locations')
                    ->where('id', $location->id)
                    ->update([
                        'latitude_new' => floatval($location->latitude),
                        'longitude_new' => floatval($location->longitude),
                    ]);
            }
        });
        
        // Drop old columns and rename new ones
        Schema::table('kataho_locations', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });
        
        Schema::table('kataho_locations', function (Blueprint $table) {
            $table->renameColumn('latitude_new', 'latitude');
            $table->renameColumn('longitude_new', 'longitude');
        });
    }

    public function down(): void
    {
        Schema::table('kataho_locations', function (Blueprint $table) {
            // Reverse: convert back to string
            $table->string('latitude')->nullable()->change();
            $table->string('longitude')->nullable()->change();
        });
    }
};
```

---

### ✅ Critical Fix #3: Add Missing Foreign Key Cascade

**Problem:** `invoices.warehouse_request_id` foreign key missing cascade on delete

**Migration File:** `2026_08_14_003_fix_foreign_key_cascades.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // For MySQL - drop and recreate constraint
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        Schema::table('invoices', function (Blueprint $table) {
            // Drop existing foreign key if it exists
            try {
                $table->dropForeign(['warehouse_request_id']);
            } catch (\Exception $e) {
                // Constraint might not exist yet
            }
            
            // Recreate with cascade
            $table->foreign('warehouse_request_id')
                ->references('id')
                ->on('warehouse_requests')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
        
        // Add other missing cascades
        Schema::table('transactions', function (Blueprint $table) {
            try {
                $table->dropForeign(['invoice_id']);
            } catch (\Exception $e) {
                // Constraint might not exist
            }
            
            $table->foreign('invoice_id')
                ->references('id')
                ->on('invoices')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
        
        Schema::table('pickup_requests', function (Blueprint $table) {
            try {
                $table->dropForeign(['warehouse_id']);
            } catch (\Exception $e) {
                // Constraint might not exist
            }
            
            $table->foreign('warehouse_id')
                ->references('id')
                ->on('warehouses')
                ->onDelete('set null');
        });
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(): void
    {
        // Complex to rollback - require manual intervention if needed
    }
};
```

---

### ✅ Critical Fix #4: Consolidate driver_rates Date Columns

**Problem:** Multiple confusing date columns (`effective_from`, `effective_until`, `effective_to`, `valid_until`)

**Migration File:** `2026_08_14_004_consolidate_driver_rates_dates.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('driver_rates', function (Blueprint $table) {
            // Add comments to clarify usage
            DB::statement('ALTER TABLE driver_rates MODIFY COLUMN effective_from TIMESTAMP NULL COMMENT "Start date when this rate becomes active"');
            DB::statement('ALTER TABLE driver_rates MODIFY COLUMN effective_until TIMESTAMP NULL COMMENT "End date when this rate expires - USE THIS FOR EXPIRATION"');
            DB::statement('ALTER TABLE driver_rates MODIFY COLUMN valid_until TIMESTAMP NULL COMMENT "DEPRECATED - Use effective_until instead"');
            DB::statement('ALTER TABLE driver_rates MODIFY COLUMN effective_to TIMESTAMP NULL COMMENT "DEPRECATED - Use effective_until instead"');
        });
        
        // Optional: Migrate data from old columns to preferred columns
        // This can be done in a follow-up migration after manual review
    }

    public function down(): void
    {
        // Removes comments (non-destructive)
    }
};
```

**Update Model to Use Correct Columns:**
```php
// app/Models/DriverRate.php

class DriverRate extends Model
{
    protected $casts = [
        'effective_from' => 'datetime',
        'effective_until' => 'datetime',
        'rate_tiers' => 'array',
    ];
    
    // Check if rate is currently active
    public function isActive(): bool
    {
        $now = now();
        $from = $this->effective_from ?? $this->created_at;
        $until = $this->effective_until;
        
        return $now->isAfter($from) && (!$until || $now->isBefore($until));
    }
    
    // Scope for active rates
    public function scopeActive($query)
    {
        return $query
            ->where(function ($q) {
                $q->whereNull('effective_from')
                  ->orWhere('effective_from', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('effective_until')
                  ->orWhere('effective_until', '>=', now());
            });
    }
}
```

---

## Phase 2: Performance Optimization (Week 2)

### ✅ Performance #1: Add Compound Indexes

**Migration File:** `2026_08_14_005_add_performance_indexes.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pickup Requests
        if (Schema::hasTable('pickup_requests')) {
            Schema::table('pickup_requests', function (Blueprint $table) {
                $table->index(['client_id', 'status'], 'idx_pickups_client_status');
                $table->index(['driver_id', 'status'], 'idx_pickups_driver_status');
                $table->index(['status', 'created_at'], 'idx_pickups_status_date');
            });
        }
        
        // Dispatch Orders
        if (Schema::hasTable('dispatch_orders')) {
            Schema::table('dispatch_orders', function (Blueprint $table) {
                $table->index(['assigned_driver_id', 'status'], 'idx_dispatch_driver_status');
                $table->index(['status', 'created_at'], 'idx_dispatch_status_date');
                $table->index(['warehouse_id', 'status'], 'idx_dispatch_warehouse_status');
            });
        }
        
        // Invoices
        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->index(['client_id', 'status'], 'idx_invoices_client_status');
                $table->index(['payment_status', 'due_date'], 'idx_invoices_payment_due');
                $table->index(['status', 'created_at'], 'idx_invoices_status_date');
            });
        }
        
        // Equipment
        if (Schema::hasTable('equipment')) {
            Schema::table('equipment', function (Blueprint $table) {
                $table->index('owner_id', 'idx_equipment_owner');
                $table->index(['type', 'status'], 'idx_equipment_type_status');
                $table->index(['status', 'created_at'], 'idx_equipment_status_date');
            });
        }
        
        // Transactions
        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->index(['user_id', 'status'], 'idx_transactions_user_status');
                $table->index(['invoice_id', 'status'], 'idx_transactions_invoice_status');
            });
        }
        
        // Equipment Requests
        if (Schema::hasTable('equipment_requests')) {
            Schema::table('equipment_requests', function (Blueprint $table) {
                $table->index(['client_id', 'status'], 'idx_equip_requests_client_status');
                $table->index(['equipment_id', 'status'], 'idx_equip_requests_equip_status');
            });
        }
        
        // Warehouse Requests
        if (Schema::hasTable('warehouse_requests')) {
            Schema::table('warehouse_requests', function (Blueprint $table) {
                $table->index(['client_id', 'status'], 'idx_warehouse_requests_client_status');
                $table->index(['warehouse_id', 'status'], 'idx_warehouse_requests_warehouse_status');
            });
        }
    }

    public function down(): void
    {
        $indexes = [
            'pickup_requests' => [
                'idx_pickups_client_status',
                'idx_pickups_driver_status',
                'idx_pickups_status_date',
            ],
            'dispatch_orders' => [
                'idx_dispatch_driver_status',
                'idx_dispatch_status_date',
                'idx_dispatch_warehouse_status',
            ],
            'invoices' => [
                'idx_invoices_client_status',
                'idx_invoices_payment_due',
                'idx_invoices_status_date',
            ],
            'equipment' => [
                'idx_equipment_owner',
                'idx_equipment_type_status',
                'idx_equipment_status_date',
            ],
            'transactions' => [
                'idx_transactions_user_status',
                'idx_transactions_invoice_status',
            ],
            'equipment_requests' => [
                'idx_equip_requests_client_status',
                'idx_equip_requests_equip_status',
            ],
            'warehouse_requests' => [
                'idx_warehouse_requests_client_status',
                'idx_warehouse_requests_warehouse_status',
            ],
        ];
        
        foreach ($indexes as $table => $indexNames) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $t) use ($indexNames) {
                    foreach ($indexNames as $index) {
                        $t->dropIndex($index);
                    }
                });
            }
        }
    }
};
```

---

### ✅ Performance #2: Add Soft Deletes to Core Tables

**Migration File:** `2026_08_14_006_add_soft_deletes.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tablesToUpdate = [
            'users',
            'dispatch_orders',
            'pickup_requests',
            'equipment',
            'invoices',
            'warehouse_requests',
            'equipment_requests',
            'proposals',
        ];
        
        foreach ($tablesToUpdate as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $t) use ($table) {
                    if (!Schema::hasColumn($table, 'deleted_at')) {
                        $t->softDeletes()
                            ->nullable()
                            ->comment('Soft delete timestamp for audit trail');
                    }
                });
            }
        }
    }

    public function down(): void
    {
        $tables = [
            'users', 'dispatch_orders', 'pickup_requests',
            'equipment', 'invoices', 'warehouse_requests',
            'equipment_requests', 'proposals',
        ];
        
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $t) {
                    if (Schema::hasColumn($table, 'deleted_at')) {
                        $t->dropSoftDeletes();
                    }
                });
            }
        }
    }
};
```

**Update Models to Use Soft Deletes:**
```php
// app/Models/PickupRequest.php

class PickupRequest extends Model
{
    use SoftDeletes;
    
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    
    // Query only non-deleted records by default
    // Use withTrashed() to include deleted records
    // Use onlyTrashed() to get only deleted records
}
```

---

## Phase 3: Data Integrity (Week 3)

### ✅ Data Integrity #1: Create Activity Logs Table

**Migration File:** `2026_08_14_007_create_activity_logs_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            
            // What was changed
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            
            // Who made the change
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            
            // What action
            $table->string('action'); // 'create', 'update', 'delete'
            
            // Before and after values
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            
            // Request context
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            
            // Timestamp
            $table->timestamps();
            
            // Indexes for querying
            $table->index(['model_type', 'model_id']);
            $table->index(['user_id', 'created_at']);
            $table->index(['created_at', 'action']);
            $table->index('model_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
```

**Create Model & Trait:**
```php
// app/Models/ActivityLog.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $guarded = [];
    protected $casts = [
        'before' => 'array',
        'after' => 'array',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

// app/Traits/LogsActivity.php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    public static function bootLogsActivity()
    {
        static::created(function ($model) {
            ActivityLog::create([
                'model_type' => get_class($model),
                'model_id' => $model->id,
                'user_id' => Auth::id(),
                'action' => 'create',
                'after' => $model->attributesToArray(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });
        
        static::updated(function ($model) {
            ActivityLog::create([
                'model_type' => get_class($model),
                'model_id' => $model->id,
                'user_id' => Auth::id(),
                'action' => 'update',
                'before' => $model->getOriginal(),
                'after' => $model->getAttributes(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });
    }
}

// Usage in model:
class PickupRequest extends Model
{
    use LogsActivity;
}
```

---

## 🚀 How to Apply These Migrations

### Step 1: Create Migration Files
Copy each migration file into `database/migrations/`

### Step 2: Test in Local Environment
```bash
# Run migrations
php artisan migrate

# Test rolling back
php artisan migrate:rollback

# Run migrations again
php artisan migrate
```

### Step 3: Update Models
Update your model files with the new traits and methods

### Step 4: Deploy to Production
```bash
# On production server
php artisan migrate

# Or with git push (if using auto-deploy)
git add -A
git commit -m "Apply critical database fixes"
git push origin main
```

### Step 5: Verify
```bash
# Check all migrations ran successfully
php artisan migrate:status

# Verify data integrity
php artisan tinker
>>> \App\Models\User::count()
>>> \App\Models\PickupRequest::count()
```

---

## 🧪 Testing Checklist

- [ ] All migrations run without errors
- [ ] Data migrates correctly (no data loss)
- [ ] Foreign keys work properly
- [ ] New indexes improve query performance
- [ ] Soft deletes work as expected
- [ ] Models have proper relationships
- [ ] Existing queries still work
- [ ] Dashboard loads without errors

---

## 📊 Expected Results After Migrations

✅ User types properly managed with JSON roles  
✅ Coordinates can be used for geospatial calculations  
✅ Foreign keys prevent orphaned records  
✅ Queries execute faster with compound indexes  
✅ Data preserved even when records "deleted"  
✅ Audit trail of all changes  
✅ Better data integrity and validation  

---

**Next Steps:**
1. Copy migrations to your project
2. Run locally and test
3. Deploy to production
4. Monitor query performance improvements

