<?php

namespace Database\Factories;

use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        $ticketNames = ['Jednorazowy Przejazd', 'Bilet 24-godzinny', 'Karnet Tygodniowy', 'Bilet Miesięczny', 'Bilet Ulgowy Studencki'];
        $randomName = fake()->unique()->randomElement($ticketNames);

        return [
            'ticket_name' => $randomName,
            'description' => fake()->sentence(10),
            'price' => fake()->randomFloat(2, 2.50, 150.00),
            'validity_hours' => fake()->randomElement([1, 2, 6, 24, 24*7, 24*30]),
            'is_active' => fake()->boolean(90),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
