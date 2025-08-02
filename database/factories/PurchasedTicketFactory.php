<?php

namespace Database\Factories;

use App\Models\PurchasedTicket;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchasedTicketFactory extends Factory
{
    protected $model = PurchasedTicket::class;

    public function definition(): array
    {
        $ticket = Ticket::inRandomOrder()->first() ?? Ticket::factory()->create();
        $user = User::inRandomOrder()->first() ?? User::factory()->create();

        $validFrom = fake()->dateTimeThisMonth();
        $status = 'aktywny';

        return [
            'ticket_id' => $ticket->ticket_id,
            'user_id' => $user->user_id,
            'valid_from' => $validFrom,
            'valid_until' => Carbon::instance($validFrom)->addHours($ticket->validity_hours),
            'status' => $status,
            'created_at' => $validFrom,
            'updated_at' => $validFrom,
        ];
    }
}
