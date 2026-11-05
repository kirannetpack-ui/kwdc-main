<?php

namespace App\Services;

use App\Models\Warehouse;
use App\Models\WarehouseRequest;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class WarehouseAvailability
{
    public function search(array $filters = [])
    {
        $start = isset($filters['start_date']) ? \Illuminate\Support\Carbon::parse($filters['start_date'])->startOfDay() : today();
        $end = isset($filters['end_date']) ? \Illuminate\Support\Carbon::parse($filters['end_date'])->startOfDay() : $start;

        $warehouses = Warehouse::where('status', 'approved')->orderBy('name')
            ->when(!empty($filters['location']), function ($query) use ($filters) {
                $term = '%'.mb_strtolower($filters['location']).'%';
                $query->where(fn ($match) => $match->whereRaw('LOWER(location) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(name) LIKE ?', [$term])->orWhereRaw('LOWER(address) LIKE ?', [$term]));
            })
            ->get();

        $ids = $warehouses->modelKeys();
        $agreements = WarehouseRequest::whereIn('status', ['assigned', 'reserved'])
            ->where(fn ($query) => $query->whereIn('assigned_warehouse_id', $ids)
                ->orWhere(fn ($original) => $original->whereNull('assigned_warehouse_id')->whereIn('warehouse_id', $ids)))
            ->get()
            ->groupBy(fn (WarehouseRequest $agreement) => $agreement->assigned_warehouse_id ?? $agreement->warehouse_id);

        return $warehouses->each(function (Warehouse $warehouse) use ($filters, $start, $end, $agreements) {
                $occupancy = $agreements->get($warehouse->id, collect());
                $warehouse->current_availability = $this->snapshot($warehouse, agreements: $occupancy);
                $warehouse->available_area = $this->snapshot($warehouse, $start, $end, agreements: $occupancy)['available'];
                if (isset($filters['lat'], $filters['lng'], $warehouse->latitude, $warehouse->longitude)) {
                    $lat = deg2rad((float) $filters['lat']);
                    $target = deg2rad((float) $warehouse->latitude);
                    $delta = deg2rad((float) $warehouse->longitude - (float) $filters['lng']);
                    $warehouse->distance = 6371 * acos(max(-1, min(1, sin($lat) * sin($target) + cos($lat) * cos($target) * cos($delta))));
                }
            })
            ->filter(fn (Warehouse $warehouse) => ($filters['include_unavailable'] ?? false) || ($warehouse->available_area > 0
                && $warehouse->available_area >= ($filters['min_area'] ?? 0)
                && (!isset($filters['lat'], $filters['lng']) || ($warehouse->distance ?? INF) <= ($filters['radius'] ?? 10))))
            ->when(isset($filters['lat'], $filters['lng']), fn ($warehouses) => $warehouses->sortBy('distance'))
            ->values();
    }

    public function occupiedFor(Warehouse $warehouse)
    {
        return WarehouseRequest::whereIn('status', ['assigned', 'reserved'])
            ->where(fn ($query) => $query->where('assigned_warehouse_id', $warehouse->id)
                ->orWhere(fn ($original) => $original->whereNull('assigned_warehouse_id')->where('warehouse_id', $warehouse->id)))
            ->get();
    }

    public function snapshot(Warehouse $warehouse, ?CarbonInterface $start = null, ?CarbonInterface $through = null, ?int $except = null, ?Collection $agreements = null): array
    {
        $start ??= today();
        $today = today();
        $through ??= $start;
        $capacity = (float) ($warehouse->area_sqft ?: $warehouse->area ?: ((float) $warehouse->area_sqm * 10.7639));
        $remaining = $capacity;
        $upcoming = [];
        $expiries = [];

        foreach ($agreements ?? $this->occupiedFor($warehouse) as $agreement) {
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
