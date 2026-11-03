<?php

namespace App\Listeners;

use App\Events\OrderDelivered;
use App\Models\PartnerEarning;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreatePartnerEarning
{
    /**
     * Handle the event.
     */
    public function handle(OrderDelivered $event): void
    {
        $order = $event->order;
        $orderType = $event->orderType;

        $partnerId = null;
        $amount = 0;

        // Extract partner and amount based on order type
        if ($orderType === 'dispatch') {
            $partnerId = $order->driver_id;
            $amount = $order->driver_earning ?? 0;
        } elseif ($orderType === 'pickup') {
            $partnerId = $order->driver_id;
            $amount = $order->driver_earning ?? 0;
        }

        // Only save if we have a valid partner and an amount > 0
        if ($partnerId && $amount > 0) {
            PartnerEarning::create([
                'partner_id' => $partnerId,
                'order_type' => $orderType,
                'order_id' => $order->id,
                'amount' => $amount,
                'status' => 'pending',
                'earned_at' => now(),
                'notes' => "Earnings for {$orderType} #{$order->id}",
            ]);
        }
    }
}