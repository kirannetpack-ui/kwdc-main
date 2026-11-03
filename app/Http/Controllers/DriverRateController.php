<?php

namespace App\Http\Controllers;

use App\Models\DriverRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DriverRateController extends Controller
{
    /**
     * Display driver rates index page
     */
    public function index()
    {
        $userId = auth()->id();
        
        // Get current active rate - using user_id instead of driver_id
        $currentRate = DriverRate::where('user_id', $userId)
            ->where('is_active', true)
            ->where(function($query) {
                $query->whereNull('valid_until')
                    ->orWhere('valid_until', '>', now());
            })
            ->first();
        
        // Get rate history
        $rates = DriverRate::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('driver.rates', compact('currentRate', 'rates'));
    }

    /**
     * Store a new driver rate
     */
    public function store(Request $request)
    {
        $request->validate([
            'rate_0_5' => 'required|numeric|min:0',
            'rate_6_10' => 'required|numeric|min:0',
            'rate_11_15' => 'required|numeric|min:0',
            'rate_16_20' => 'required|numeric|min:0',
            'rate_per_km' => 'required|numeric|min:0',
            'valid_until' => 'nullable|date|after:today',
            'vehicle_type' => 'nullable|string|max:50',
        ]);

        // Deactivate all existing rates for this driver
        DriverRate::where('user_id', auth()->id())->update(['is_active' => false]);

        // Create rate_tiers JSON
        $rateTiers = [
            'tier1' => $request->rate_0_5,      // 0-5 km
            'tier2' => $request->rate_6_10,     // 6-10 km
            'tier3' => $request->rate_11_15,    // 11-15 km
            'tier4' => $request->rate_16_20,    // 16-20 km
            'tier4_per_km' => $request->rate_per_km, // 21+ km per km rate
            'base_price' => $request->rate_0_5,
        ];

        // Create new rate
        DriverRate::create([
            'user_id' => auth()->id(),
            'driver_id' => auth()->id(), // For backward compatibility
            'date' => now()->toDateString(),
            'base_price' => $request->rate_0_5,
            'rate_tiers' => json_encode($rateTiers),
            'vehicle_type' => $request->vehicle_type ?? 'Standard',
            'valid_until' => $request->valid_until ? date('Y-m-d H:i:s', strtotime($request->valid_until . ' 23:59:59')) : null,
            'is_active' => true,
            'status' => 'active',
        ]);

        return redirect()->route('driver.rates')
            ->with('success', 'Rate added successfully!');
    }

    /**
     * Extend an existing rate
     */
    public function extend(Request $request)
    {
        $request->validate([
            'rate_id' => 'required|exists:driver_rates,id',
            'valid_until' => 'required|date|after:today'
        ]);

        $rate = DriverRate::where('user_id', auth()->id())
            ->where('id', $request->rate_id)
            ->firstOrFail();
        
        $rate->valid_until = date('Y-m-d H:i:s', strtotime($request->valid_until . ' 23:59:59'));
        $rate->is_active = true;
        $rate->save();

        return redirect()->route('driver.rates')
            ->with('success', 'Rate extended successfully!');
    }

    /**
     * Calculate price based on distance
     */
    public function calculatePrice(Request $request)
    {
        $request->validate([
            'distance' => 'required|numeric|min:0',
            'driver_id' => 'nullable|exists:users,id'
        ]);

        $driverId = $request->driver_id ?? auth()->id();
        
        // Get active rate for the driver
        $rate = DriverRate::where('user_id', $driverId)
            ->where('is_active', true)
            ->where(function($query) {
                $query->whereNull('valid_until')
                    ->orWhere('valid_until', '>', now());
            })
            ->first();

        if (!$rate) {
            return response()->json([
                'success' => false,
                'error' => 'No active rate found for this driver'
            ], 404);
        }

        $price = $this->calculatePriceFromRate($rate, $request->distance);
        
        return response()->json([
            'success' => true,
            'distance' => $request->distance,
            'price' => $price,
            'price_formatted' => 'रु ' . number_format($price, 2),
            'rate_applied' => $this->getRateApplied($rate, $request->distance)
        ]);
    }

    /**
     * Calculate price from rate object
     */
    private function calculatePriceFromRate($rate, $distance)
    {
        // Get rate tiers from JSON or use individual columns
        $tiers = $rate->rate_tiers;
        
        if ($tiers) {
            $tiers = is_array($tiers) ? $tiers : json_decode($tiers, true);
            
            if ($distance <= 5) {
                return $tiers['tier1'] ?? 300;
            } elseif ($distance <= 10) {
                return $tiers['tier2'] ?? 500;
            } elseif ($distance <= 15) {
                return $tiers['tier3'] ?? 800;
            } elseif ($distance <= 20) {
                return $tiers['tier4'] ?? 1000;
            } else {
                $basePrice = $tiers['tier4'] ?? 1000;
                $extraKm = $distance - 20;
                $perKmRate = $tiers['tier4_per_km'] ?? 40;
                return $basePrice + ($extraKm * $perKmRate);
            }
        }
        
        // Fallback to individual column checks
        if (property_exists($rate, 'rate_0_5') && $rate->rate_0_5) {
            if ($distance <= 5) return $rate->rate_0_5;
            if ($distance <= 10) return $rate->rate_6_10 ?? 500;
            if ($distance <= 15) return $rate->rate_11_15 ?? 800;
            if ($distance <= 20) return $rate->rate_16_20 ?? 1000;
            $extraKm = $distance - 20;
            return ($rate->rate_16_20 ?? 1000) + ($extraKm * ($rate->rate_per_km ?? 40));
        }
        
        // Default pricing
        if ($distance <= 5) return 300;
        if ($distance <= 10) return 500;
        if ($distance <= 15) return 800;
        if ($distance <= 20) return 1000;
        return 1000 + (($distance - 20) * 40);
    }

    /**
     * Get rate applied description
     */
    private function getRateApplied($rate, $distance)
    {
        $tiers = $rate->rate_tiers;
        
        if ($tiers) {
            $tiers = is_array($tiers) ? $tiers : json_decode($tiers, true);
            
            if ($distance <= 5) {
                return "0-5 km: रु " . number_format($tiers['tier1'] ?? 300, 2);
            } elseif ($distance <= 10) {
                return "6-10 km: रु " . number_format($tiers['tier2'] ?? 500, 2);
            } elseif ($distance <= 15) {
                return "11-15 km: रु " . number_format($tiers['tier3'] ?? 800, 2);
            } elseif ($distance <= 20) {
                return "16-20 km: रु " . number_format($tiers['tier4'] ?? 1000, 2);
            } else {
                $remaining = $distance - 20;
                $basePrice = $tiers['tier4'] ?? 1000;
                $perKmRate = $tiers['tier4_per_km'] ?? 40;
                return "16-20 km: रु " . number_format($basePrice, 2) . " + " . $remaining . " km × रु " . number_format($perKmRate, 2);
            }
        }
        
        // Fallback to individual columns
        if ($distance <= 5) {
            return "0-5 km: रु " . number_format($rate->rate_0_5 ?? 300, 2);
        } elseif ($distance <= 10) {
            return "6-10 km: रु " . number_format($rate->rate_6_10 ?? 500, 2);
        } elseif ($distance <= 15) {
            return "11-15 km: रु " . number_format($rate->rate_11_15 ?? 800, 2);
        } elseif ($distance <= 20) {
            return "16-20 km: रु " . number_format($rate->rate_16_20 ?? 1000, 2);
        } else {
            $remaining = $distance - 20;
            return "16-20 km: रु " . number_format($rate->rate_16_20 ?? 1000, 2) . " + " . $remaining . " km × रु " . number_format($rate->rate_per_km ?? 40, 2);
        }
    }

    /**
     * Get rate history
     */
    public function history()
    {
        $rates = DriverRate::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('driver.rates-history', compact('rates'));
    }
}