<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LocationUpdated implements ShouldBroadcast   // changed from ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $dispatchId;
    public $latitude;
    public $longitude;
    public $eta;
    public $clientId;

    public function __construct($dispatchId, $latitude, $longitude, $eta = null, $clientId = null)
    {
        $this->dispatchId = $dispatchId;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->eta = $eta;
        $this->clientId = $clientId;
    }

    public function broadcastOn()
    {
        $channels = [
            new PrivateChannel('dispatch.' . $this->dispatchId)
        ];

        // Broadcast to client's personal notification channel if clientId exists
        if ($this->clientId) {
            $channels[] = new PrivateChannel('notifications.' . $this->clientId);
        }

        return $channels;
    }

    public function broadcastWith()
    {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'eta' => $this->eta ? $this->eta->toIso8601String() : null,
            'message' => 'Driver location updated',
        ];
    }
}