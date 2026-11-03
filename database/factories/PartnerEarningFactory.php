<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\DispatchOrder;
use App\Models\PartnerEarning;
use Illuminate\Database\Eloquent\Factories\Factory;

class PartnerEarningFactory extends Factory
{
    protected $model = PartnerEarning::class;

    public function definition(): array
    {
        return [
            'driver_id' => User::factory()->create(['role' => 'driver']),
            'dispatch_id' => DispatchOrder::factory(),
            'amount' => $this->faker->numberBetween(100, 1000),
            'status' => 'pending',
        ];
    }
}