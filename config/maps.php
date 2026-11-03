<?php

return [
    // Public demo services are for local evaluation; use dedicated endpoints for launch.
    'geocoder_url' => env('MAP_GEOCODER_URL', 'https://photon.komoot.io'),
    'router_url' => env('MAP_ROUTER_URL', 'https://router.project-osrm.org'),
    'timeout' => 8,
    'cache_seconds' => 86400,
];
