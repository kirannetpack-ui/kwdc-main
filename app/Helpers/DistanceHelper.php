<?php

namespace App\Helpers;

class DistanceHelper
{
    // New pricing tiers based on requirements
    const PRICE_TIERS = [
        ['min' => 0, 'max' => 5, 'type' => 'flat', 'rate_field' => 'flat_rate_0_5'],
        ['min' => 6, 'max' => 10, 'type' => 'flat', 'rate_field' => 'flat_rate_6_10'],
        ['min' => 11, 'max' => 20, 'type' => 'flat', 'rate_field' => 'flat_rate_11_20'],
        ['min' => 21, 'max' => 999999, 'type' => 'per_km', 'rate_field' => 'rate_per_km_21_plus'],
    ];

    /**
     * Calculate distance between two coordinates using Haversine formula
     */
    public static function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        if (!$lat1 || !$lon1 || !$lat2 || !$lon2) {
            return null;
        }
        
        $earthRadius = 6371; // km
        
        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);
        
        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lonDelta / 2) * sin($lonDelta / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return round($earthRadius * $c, 2);
    }

    /**
     * Calculate price based on distance using driver's rates
     */
    public static function calculatePrice($distance, $driverRate = null)
    {
        if (!$distance) return null;
        
        // Determine which tier the distance falls into
        $tier = self::getTierForDistance($distance);
        
        if (!$tier) {
            return null;
        }
        
        if ($tier['type'] == 'flat') {
            // Flat rate pricing
            if ($driverRate) {
                $rateField = $tier['rate_field'];
                return $driverRate->$rateField ?? 0;
            }
            // Default flat rates
            $defaultRates = [
                'flat_rate_0_5' => 200,
                'flat_rate_6_10' => 300,
                'flat_rate_11_20' => 500,
            ];
            return $defaultRates[$tier['rate_field']] ?? 0;
        } else {
            // Per km pricing for distances 21+ km
            $rate = $driverRate ? ($driverRate->rate_per_km_21_plus ?? 35) : 35;
            return round($distance * $rate, 2);
        }
    }

    /**
     * Get the tier for a given distance
     */
    private static function getTierForDistance($distance)
    {
        foreach (self::PRICE_TIERS as $tier) {
            if ($distance >= $tier['min'] && $distance <= $tier['max']) {
                return $tier;
            }
        }
        return null;
    }

    /**
     * Get price breakdown for display
     */
    public static function getPriceBreakdown($distance, $driverRate = null)
    {
        $price = self::calculatePrice($distance, $driverRate);
        $tier = self::getTierForDistance($distance);
        
        if (!$tier) {
            return null;
        }
        
        $breakdown = '';
        if ($tier['type'] == 'flat') {
            if ($distance <= 5) {
                $breakdown = "Flat rate for 0-5 km: रु " . number_format($price);
            } elseif ($distance <= 10) {
                $breakdown = "Flat rate for 6-10 km: रु " . number_format($price);
            } elseif ($distance <= 20) {
                $breakdown = "Flat rate for 11-20 km: रु " . number_format($price);
            }
        } else {
            $rate = $driverRate ? ($driverRate->rate_per_km_21_plus ?? 35) : 35;
            $breakdown = "{$distance} km × रु {$rate}/km = रु " . number_format($price);
        }
        
        return [
            'distance' => $distance,
            'price' => $price,
            'tier' => $tier,
            'breakdown' => $breakdown
        ];
    }
}