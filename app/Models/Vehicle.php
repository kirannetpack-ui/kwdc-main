<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'driver_id',
        'driver_code',
        'vehicle_number',
        'vehicle_type',
        'custom_vehicle_type',
        'capacity',
        'capacity_unit',
        'manufacturer',
        'model',
        'year',
        'color',
        'fuel_type',
        'registration_number',
        'registration_date',
        'insurance_number',
        'insurance_valid_until',
        'fitness_certificate_number',
        'fitness_valid_until',
        'pollution_certificate_number',
        'pollution_valid_until',
        'permit_number',
        'permit_valid_until',
        'blue_book_number',
        'insurance_file_path',
        'fitness_file_path',
        'pollution_file_path',
        'permit_file_path',
        'blue_book_file_path',
        'front_photo_path',
        'back_photo_path',
        'left_photo_path',
        'right_photo_path',
        'interior_photo_path',
        'status',
        'is_verified',
        'verified_by',
        'verified_at',
        'rejection_reason',
        'description',
    ];

    protected $casts = [
        'registration_date' => 'date',
        'insurance_valid_until' => 'date',
        'fitness_valid_until' => 'date',
        'pollution_valid_until' => 'date',
        'permit_valid_until' => 'date',
        'verified_at' => 'datetime',
        'is_verified' => 'boolean',
        'capacity' => 'decimal:2',
    ];

    // Relationship to the owner (user_id)
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relationship to the driver (driver_id)
    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    // Generate unique vehicle registration number
    public static function generateVehicleCode()
    {
        $year = now()->year;
        $lastVehicle = self::where('registration_number', 'like', "VEH-{$year}-%")
            ->orderBy('registration_number', 'desc')
            ->first();

        if ($lastVehicle) {
            $lastNumber = intval(substr($lastVehicle->registration_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "VEH-{$year}-{$newNumber}";
    }

    /**
     * Check if vehicle has all required regulatory documents valid.
     */
    public function isCompliant(): bool
    {
        $now = now()->startOfDay();

        if ($this->insurance_valid_until && $this->insurance_valid_until < $now) return false;
        if ($this->fitness_valid_until && $this->fitness_valid_until < $now) return false;
        if ($this->pollution_valid_until && $this->pollution_valid_until < $now) return false;
        if ($this->permit_valid_until && $this->permit_valid_until < $now) return false;

        return true;
    }

    /**
     * Get list of compliance issues or upcoming document expirations.
     */
    public function getComplianceIssues(): array
    {
        $issues = [];
        $now = now()->startOfDay();

        $docs = [
            'Insurance Policy' => $this->insurance_valid_until,
            'Fitness / Blue Book' => $this->fitness_valid_until,
            'Pollution Sticker' => $this->pollution_valid_until,
            'Route Permit' => $this->permit_valid_until,
        ];

        foreach ($docs as $name => $date) {
            if (!$date) continue;
            if ($date < $now) {
                $issues[] = ['doc' => $name, 'status' => 'expired', 'date' => $date->format('Y-m-d')];
            } elseif ($date <= $now->copy()->addDays(15)) {
                $days = (int) $now->diffInDays($date);
                $issues[] = ['doc' => $name, 'status' => 'expiring', 'days' => $days, 'date' => $date->format('Y-m-d')];
            }
        }

        return $issues;
    }
}