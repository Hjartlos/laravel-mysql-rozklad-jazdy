<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TripsSeeder extends Seeder
{
    public function run()
    {
        $trips = [];
        $tripId = 1;

        for ($lineId = 1; $lineId <= 3; $lineId++) {
            for ($dayId = 1; $dayId <= 3; $dayId++) {
                $trips[] = [
                    'trip_id' => $tripId++,
                    'line_id' => $lineId,
                    'day_id' => $dayId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::table('trips')->insert($trips);
    }
}
