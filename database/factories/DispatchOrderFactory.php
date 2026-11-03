<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\DispatchOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

class DispatchOrderFactory extends Factory
{
    protected $model = DispatchOrder::class;

    public function definition(): array
    {
        return [
            'client_id' => User::factory()->create(['role' => 'client'])->id,
            'driver_id' => null, // unassigned by default
            'pickup_address' => $this->faker->address,
            'total_distance' => $this->faker->numberBetween(5, 100), // column name: total_distance
            'base_price' => $this->faker->numberBetween(500, 5000),
            'status' => 'pending',
            'current_latitude' => null,
            'current_longitude' => null,
            'tracking_id' => 'TRK-' . strtoupper($this->faker->unique()->lexify('?????')), // ensure uniqueness
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    // State for "in_progress" dispatch
    public function inProgress(): self
    {
        return $this->state(fn () => ['status' => 'in_progress']);
    }

    public function delivered(): self
    {
        return $this->state(fn () => ['status' => 'delivered']);
    }
}