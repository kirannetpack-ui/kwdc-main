<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

class WarehouseFactory extends Factory
{
    protected $model = Warehouse::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->company,
            'location' => $this->faker->city,
            'address' => $this->faker->address,
            'latitude' => $this->faker->latitude(26, 30),
            'longitude' => $this->faker->longitude(80, 88),
            // add other fields your warehouse table has
        ];
    }
}
