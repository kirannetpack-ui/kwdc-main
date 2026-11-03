<?php

namespace App\Services;

use App\Models\MarginTier;

class AdminConfigService
{
    public function getPricePerKm(): float
    {
        return 45.00; // Admin's fixed price
    }

    public function getMinimumCharge(): float
    {
        return 250.00;
    }

    /**
     * Dynamically fetch margin based on Service Type and Distance
     */
    public function getAdminMargin(float $distance, string $serviceType = 'dispatch'): array
    {
        $tier = MarginTier::where('is_active', true)
            ->where('service_type', $serviceType)
            ->where('min_distance', '<=', $distance)
            ->where(function($query) use ($distance) {
                $query->where('max_distance', '>=', $distance)
                      ->orWhereNull('max_distance');
            })
            ->first();

        // If no specific tier is found, fallback to a safe default (10% percentage)
        if (!$tier) {
            return [
                'type' => 'percentage',
                'value' => 10.00,
            ];
        }

        return [
            'type' => $tier->margin_type,
            'value' => (float) $tier->margin_value,
        ];
    }
}