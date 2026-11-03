<?php

namespace App\Services;

use GuzzleHttp\Client;

class GoogleMapsService
{
    protected $client;
    protected $apiKey;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = config('services.google_maps.api_key');
    }

    /**
     * Get travel time in traffic from origin to destination.
     * Returns duration in seconds, or null on error.
     */
    public function getTrafficDuration($originLat, $originLng, $destLat, $destLng)
    {
        $url = 'https://maps.googleapis.com/maps/api/distancematrix/json';
        $query = [
            'origins' => "{$originLat},{$originLng}",
            'destinations' => "{$destLat},{$destLng}",
            'key' => $this->apiKey,
            'traffic_model' => 'best_guess',
            'departure_time' => 'now',
        ];

        try {
        $response = $this->client->get($url, ['query' => $query]);
        $data = json_decode($response->getBody(), true);
        
        // Log the full response for debugging
        \Log::info('Google Maps API response', $data);

        if ($data['status'] === 'OK') {
            // ... existing logic ...
        } else {
            \Log::warning('Google Maps API error', ['status' => $data['status'] ?? 'unknown']);
        }
    } catch (\Exception $e) {
        \Log::error('Google Maps API exception', ['message' => $e->getMessage()]);
    }
    return null;
}
}