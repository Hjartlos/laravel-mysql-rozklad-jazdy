<?php

namespace Database\Factories;

use App\Models\Line;
use Illuminate\Database\Eloquent\Factories\Factory;

class LineFactory extends Factory
{
    protected $model = Line::class;

    public function definition(): array
    {
        $lineNumber = fake()->unique()->numerify(fake()->randomElement(['##', '1##', '2##', 'N##']));
        $direction = fake()->optional(0.6)->randomElement(['Centrum', 'Os. Projektant', 'Krakowska', 'Lubelska', null]);
        $lineNamePart1 = fake()->streetName;
        $lineNamePart2 = fake()->streetName;

        return [
            'line_number' => $lineNumber,
            'line_name' => $lineNamePart1 . ' - ' . $lineNamePart2 . ($direction ? ' (' . $direction . ')' : ''),
            'direction' => $direction,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
