<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Events\DriverLocationUpdated;

class DriverLocationUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }

public function updateLocation(Request $request, $id)
{
    $request->validate([
        'latitude' => 'required|numeric|between:-90,90',
        'longitude' => 'required|numeric|between:-180,180',
    ]);

    $dispatch = DispatchOrder::findOrFail($id);

    // Authorize: only the assigned driver or admin can update
    if (Auth::id() !== $dispatch->driver_id && Auth::user()->role !== 'admin') {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    // Update the database
    $dispatch->current_latitude = $request->latitude;
    $dispatch->current_longitude = $request->longitude;
    $dispatch->last_location_update = now();
    $dispatch->save();

    // Broadcast the new coordinates to all clients listening to this dispatch
    broadcast(new DriverLocationUpdated($dispatch->id, $request->latitude, $request->longitude));

    return response()->json(['success' => true]);
}
}
