<?php

namespace Database\Factories;

use App\Models\Stop;
use Illuminate\Database\Eloquent\Factories\Factory;

class StopFactory extends Factory
{
    protected $model = Stop::class;

    public function definition(): array
    {
        return [
            'stop_name' => fake()->unique()->streetName . ' ' . fake()->randomElement(['Pętla', 'Szkoła', 'Osiedle', fake()->buildingNumber]),
            'location_lat' => fake()->latitude(49.95, 50.10),
            'location_lon' => fake()->longitude(21.85, 22.15),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
