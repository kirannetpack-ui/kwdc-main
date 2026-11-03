<?php

namespace App\Models;

use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\DispatchOrder;
use App\Notifications\ProfessionalResetPasswordNotification;

class User extends Authenticatable
{
    use HasFactory, Notifiable, MustVerifyEmailTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'user_code',
        'is_active',                // Added to match your DB schema
        'avg_rating',               // Added to match your DB schema
        'address',                  // Added to match your DB schema
        'profile_photo',            // Added to match your DB schema
        'is_admin',
        'is_client',
        'is_driver',
        'is_property_owner',
        'is_equipment_owner',
        'user_type',                // Added to match your DB schema
        'preferred_location',       // Added to match your DB schema
        'email_verified_at',
        'activation_code_hash',
        'activation_expires_at',
        'is_banned',
        'ban_reason',
        'banned_at',
        'two_factor_enabled',
        'two_factor_code',
        'two_factor_expires_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'activation_code_hash',
        'two_factor_code',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'activation_expires_at' => 'datetime',
        'banned_at' => 'datetime',
        'two_factor_expires_at' => 'datetime',
        'two_factor_enabled' => 'boolean',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'is_banned' => 'boolean',
        'is_admin' => 'boolean',
        'is_client' => 'boolean',
        'is_driver' => 'boolean',
        'is_property_owner' => 'boolean',
        'is_equipment_owner' => 'boolean',
        'avg_rating' => 'decimal:1',
    ];

    /**
     * Ban the user account
     */
    public function ban(?string $reason = null): self
    {
        $this->forceFill([
            'is_banned' => true,
            'is_active' => false,
            'ban_reason' => $reason ?: 'Account suspended due to security compliance review.',
            'banned_at' => now(),
        ])->save();

        return $this;
    }

    /**
     * Unban the user account
     */
    public function unban(): self
    {
        $this->forceFill([
            'is_banned' => false,
            'is_active' => true,
            'ban_reason' => null,
            'banned_at' => null,
        ])->save();

        return $this;
    }

    /**
     * Check if user is banned
     */
    public function isBanned(): bool
    {
        return (bool) $this->is_banned;
    }

    /**
     * Generate and persist a 6-digit 2FA code valid for 10 minutes.
     */
    public function generateTwoFactorCode(): string
    {
        $code = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        $this->forceFill([
            'two_factor_code' => $code,
            'two_factor_expires_at' => now()->addMinutes(10),
        ])->save();

        return $code;
    }

    /**
     * Verify submitted 2FA code.
     */
    public function verifyTwoFactorCode(?string $code): bool
    {
        if (!$code || !$this->two_factor_code || !$this->two_factor_expires_at) {
            return false;
        }

        if ($this->two_factor_expires_at < now()) {
            return false;
        }

        if ($this->two_factor_code === trim($code)) {
            $this->forceFill([
                'two_factor_code' => null,
                'two_factor_expires_at' => null,
            ])->save();
            return true;
        }

        return false;
    }

    /**
     * Enable Two-Factor Authentication.
     */
    public function enableTwoFactor(): self
    {
        $this->forceFill(['two_factor_enabled' => true])->save();
        return $this;
    }

    /**
     * Disable Two-Factor Authentication.
     */
    public function disableTwoFactor(): self
    {
        $this->forceFill([
            'two_factor_enabled' => false,
            'two_factor_code' => null,
            'two_factor_expires_at' => null,
        ])->save();
        return $this;
    }

    // ============================================================
    // 🚀 ROLE HELPER METHODS (Fixes your dashboard error!)
    // ============================================================

    /**
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin' || $this->is_admin === true;
    }

    /**
     * Check if the user is a client.
     */
    public function isClient(): bool
    {
        return $this->role === 'client';
    }

    /**
     * Check if the user is a driver.
     */
    public function isDriver(): bool
    {
        return $this->role === 'driver' || $this->is_driver === true;
    }

    /**
     * Check if the user is a property owner.
     */
    public function isPropertyOwner(): bool
    {
        return $this->role === 'property_owner' || $this->is_property_owner === true;
    }

    /**
     * Check if the user is an equipment owner.
     */
    public function isEquipmentOwner(): bool
    {
        return $this->role === 'equipment_owner' || $this->is_equipment_owner === true;
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ProfessionalResetPasswordNotification($token));
    }

    // ============================================================
    // RELATIONSHIPS
    // ============================================================

    /**
     * Get the active driver rate for this user.
     */
    public function driverRate()
    {
        return $this->hasOne(DriverRate::class, 'driver_id')->where('is_active', true);
    }

    /**
     * Get all driver rates for this user.
     */
    public function driverRates()
    {
        return $this->hasMany(DriverRate::class, 'driver_id');
    }

    /**
     * Get today's active driver rate.
     */
    public function todayRate()
    {
        return $this->hasOne(DriverRate::class, 'driver_id')
            ->whereDate('date', today())
            ->where('is_active', true);
    }

    /**
     * Get the current valid driver rate.
     */
    public function currentRate()
    {
        return $this->hasOne(DriverRate::class, 'driver_id')
            ->where('is_active', true)
            ->where(function($q) {
                $q->whereNull('valid_until')
                  ->orWhere('valid_until', '>', now());
            });
    }

    /**
     * Get the warehouses owned by this user (for property owners).
     */
    public function warehouses()
    {
        return $this->hasMany(Warehouse::class, 'owner_id');
    }

    /**
     * Get the vehicles owned by this user.
     */
    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'driver_id');
    }

    /**
     * Get the equipment owned by this user.
     */
    public function equipment()
    {
        return $this->hasMany(Equipment::class, 'user_id');
    }

    /**
     * Get the proposals for this user (as client).
     */
    public function proposals()
    {
        return $this->hasMany(Proposal::class, 'client_id');
    }

    /**
     * Get the warehouse requests for this user (as client).
     */
    public function warehouseRequests()
    {
        return $this->hasMany(WarehouseRequest::class, 'client_id');
    }

    /**
     * Get the security agency profile for this user.
     */
    public function securityAgency()
    {
        return $this->hasOne(SecurityAgency::class, 'user_id');
    }

    /**
     * Get the dispatch orders where this user is the driver.
     */
    public function dispatchOrders()
    {
        return $this->hasMany(DispatchOrder::class, 'driver_id');
    }

    public function reminders()
    {
        return $this->hasMany(UserReminder::class);
    }
}
