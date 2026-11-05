<?php

namespace App\Services;

use App\Models\Warehouse;
use App\Models\WarehouseRequest;
use Carbon\CarbonInterface;

class WarehouseAvailability
{
    public function occupiedFor(Warehouse $warehouse)
    {
        return WarehouseRequest::whereIn('status', ['assigned', 'reserved'])
            ->where(fn ($query) => $query->where('assigned_warehouse_id', $warehouse->id)
                ->orWhere(fn ($original) => $original->whereNull('assigned_warehouse_id')->where('warehouse_id', $warehouse->id)))
            ->get();
    }

    public function snapshot(Warehouse $warehouse, ?CarbonInterface $start = null, ?CarbonInterface $through = null, ?int $except = null): array
    {
        $start ??= today();
        $today = today();
        $through ??= $start;
        $capacity = (float) ($warehouse->area_sqft ?: $warehouse->area ?: ((float) $warehouse->area_sqm * 10.7639));
        $remaining = $capacity;
        $upcoming = [];
        $expiries = [];

        foreach ($this->occupiedFor($warehouse) as $agreement) {
            $area = (float) ($agreement->space_required ?: $agreement->required_area ?: $capacity);
            $end = $agreement->agreementEnd();
            if ($agreement->id === $except) {
                continue;
            }
            if ($agreement->status === 'reserved') {
                if (!$agreement->renewal_of_id && $agreement->preferred_start_date?->lte($through) && (!$end || $end->gte($start))) {
                    $remaining -= $area;
                }
                continue;
            }
            if ($end && $end->gte($today)) {
                $expiries[$end->toDateString()] = true;
            }

            $released = $end && $end->gte($today) && $agreement->isOpenForPrebooking();

            if ($released) {
                $moveIn = $end->copy()->addDay();
                $key = $moveIn->toDateString();
                $upcoming[$key] = ($upcoming[$key] ?? 0) + $area;

                if ($start->gte($moveIn)) {
                    continue;
                }
            }

            $remaining -= $area;
        }

        ksort($upcoming);

        ksort($expiries);

        return ['available' => max(0, round($remaining, 2)), 'upcoming' => $upcoming, 'expiries' => array_keys($expiries)];
    }
}
