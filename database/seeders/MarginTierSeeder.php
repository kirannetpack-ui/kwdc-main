<?php

namespace Database\Seeders;

use App\Models\MarginTier;
use Illuminate\Database\Seeder;

class MarginTierSeeder extends Seeder
{
    public function run()
    {
        MarginTier::insert([
            ['min_amount' => 0, 'max_amount' => 1000, 'margin_percentage' => 20],
            ['min_amount' => 1000, 'max_amount' => 2000, 'margin_percentage' => 15],
            ['min_amount' => 2000, 'max_amount' => null, 'margin_percentage' => 10],
        ]);
    }
}