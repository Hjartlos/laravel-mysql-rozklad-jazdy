<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Stop;
use App\Models\Line;
use App\Models\Ticket;
use App\Models\PurchasedTicket;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        $user = User::inRandomOrder()->first() ?? User::factory()->create();
        $fromStop = Stop::inRandomOrder()->first() ?? Stop::factory()->create();
        $toStop = Stop::where('stop_id', '!=', $fromStop->stop_id)->inRandomOrder()->first() ?? Stop::factory()->create();
        $line = Line::inRandomOrder()->first() ?? Line::factory()->create();

        $departureTime = fake()->dateTimeBetween('-60 days', 'now');
        $durationMinutes = fake()->numberBetween(5, 120);
        $arrivalTime = Carbon::instance($departureTime)->addMinutes($durationMinutes);

        $statuses = ['oczekująca', 'zakończona', 'anulowana', 'nieudana', 'wygasła'];
        $status = fake()->randomElement($statuses);

        return [
            'user_id' => $user->user_id,
            'from_stop_id' => $fromStop->stop_id,
            'to_stop_id' => $toStop->stop_id,
            'line_id' => $line->line_id,
            'departure_time' => $departureTime,
            'arrival_time' => $arrivalTime,
            'duration_minutes' => $durationMinutes,
            'price' => fake()->randomFloat(2, 2.00, 25.00),
            'status' => $status,
            'payment_id' => fake()->optional(0.8)->regexify('[A-Za-z0-9]{10,30}'),
            'route_data' => json_encode([
                'from' => $fromStop->stop_name,
                'to' => $toStop->stop_name,
                'line_number' => $line->line_number
            ]),
            'created_at' => $departureTime,
            'updated_at' => Carbon::instance($departureTime)->addMinutes(fake()->numberBetween(1, 60)),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Transaction $transaction) {
            $purchasedTicketStatus = match(strtolower($transaction->status)) {
                'zakończona' => 'aktywny',
                'oczekująca' => 'oczekujący',
                'anulowana' => 'anulowany',
                'wygasła' => 'wygasły',
                'nieudana' => 'anulowany',
                default => 'oczekujący'
            };

            if (in_array($transaction->status, ['zakończona', 'oczekująca', 'anulowana', 'wygasła', 'nieudana'])) {
                $ticketModel = Ticket::inRandomOrder()->first() ?? Ticket::factory()->create();
                $validFrom = Carbon::instance($transaction->created_at);

                PurchasedTicket::factory()->create([
                    'ticket_id' => $ticketModel->ticket_id,
                    'user_id' => $transaction->user_id,
                    'transaction_id' => $transaction->transaction_id,
                    'valid_from' => $validFrom,
                    'valid_until' => (clone $validFrom)->addHours($ticketModel->validity_hours),
                    'status' => $purchasedTicketStatus,
                ]);
            }
        });
    }
}
