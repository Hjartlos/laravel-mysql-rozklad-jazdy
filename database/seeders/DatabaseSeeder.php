<?php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        User::factory()->admin()->create();
        User::factory()->count(10)->create();

        $this->call([
            LinesSeeder::class,
            StopsSeeder::class,
            OperatingDaysSeeder::class,
            TripsSeeder::class,
            LineStopSeeder::class,
            DepartureTimesSeeder::class,
        ]);

        Ticket::factory()->count(5)->create();

        Transaction::factory()->count(50)->create();
    }
}
