<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\KatahoLocation;

class KatahoService
{
    protected $apiUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->apiUrl = config('services.kataho.api_url', 'https://api.kataho.app/v1');
        $this->apiKey = config('services.kataho.api_key');
    }

    /**
     * Get location details from Kataho code
     */
    public function getLocationByCode($katahoCode)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ])->get("{$this->apiUrl}/location/{$katahoCode}");

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Kataho API error: ' . $response->body());
            return null;

        } catch (\Exception $e) {
            Log::error('Kataho API exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get Kataho code from coordinates
     */
    public function getCodeFromCoordinates($latitude, $longitude)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ])->post("{$this->apiUrl}/reverse-geocode", [
                'latitude' => $latitude,
                'longitude' => $longitude,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Kataho reverse geocode error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Verify and store a Kataho location
     */
    public function verifyAndStoreLocation($katahoCode, $referenceType, $referenceId, $additionalData = [])
    {
        $locationData = $this->getLocationByCode($katahoCode);

        if (!$locationData) {
            return null;
        }

        // Check if already exists
        $existing = KatahoLocation::where('kataho_code', $katahoCode)->first();

        if ($existing) {
            return $existing;
        }

        // Create new location
        return KatahoLocation::create([
            'kataho_code' => $katahoCode,
            'grid_id' => $locationData['grid_id'] ?? null,
            'plate_id' => $locationData['plate_id'] ?? null,
            'name' => $additionalData['name'] ?? $locationData['name'] ?? null,
            'address' => $additionalData['address'] ?? $locationData['address'] ?? null,
            'latitude' => $locationData['latitude'] ?? null,
            'longitude' => $locationData['longitude'] ?? null,
            'location_type' => $referenceType,
            'reference_id' => $referenceId,
            'reference_type' => $referenceType,
            'is_verified' => true,
        ]);
    }

    /**
     * Validate Kataho code format
     */
    public function validateKatahoCode($code)
    {
        // Kataho codes are typically in format like "KH-1234-5678" or similar
        return preg_match('/^[A-Z]{2,3}-[0-9]{4,6}-[0-9]{4,6}$/', $code);
    }

    /**
     * Generate Google Maps link from Kataho data
     */
    public function getGoogleMapsLink($latitude, $longitude)
    {
        return "https://www.google.com/maps?q={$latitude},{$longitude}";
    }

    /**
     * Get navigation link using Kataho app
     */
    public function getKatahoNavigationLink($katahoCode)
    {
        return "https://kataho.app/navigate/{$katahoCode}";
    }

    /**
     * Search Kataho locations by query
     */
    public function searchLocations($query, $limit = 20)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ])->get("{$this->apiUrl}/search", [
                'q' => $query,
                'limit' => $limit,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return [];

        } catch (\Exception $e) {
            Log::error('Kataho search error: ' . $e->getMessage());
            return [];
        }
    }
}