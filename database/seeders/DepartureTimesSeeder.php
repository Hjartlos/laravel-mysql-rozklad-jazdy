<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Trip;
use App\Models\Line;
use Carbon\Carbon;

class DepartureTimesSeeder extends Seeder
{
    public function run()
    {
        $trips = Trip::all();
        $departureTimes = [];

        $startTimes = [
            1 => [
                1 => ['05:00', '06:30', '08:00', '09:30', '11:00', '12:30', '14:00', '15:30', '17:00', '18:30', '20:00'],
                2 => ['06:00', '08:00', '10:00', '12:00', '14:00', '16:00', '18:00', '20:00'],
                3 => ['07:00', '09:30', '12:00', '14:30', '17:00', '19:30'],
            ],
            2 => [
                1 => ['04:45', '06:15', '07:45', '09:15', '10:45', '12:15', '13:45', '15:15', '16:45', '18:15', '19:45', '21:15'],
                2 => ['06:15', '08:15', '10:15', '12:15', '14:15', '16:15', '18:15', '20:15'],
                3 => ['07:15', '09:45', '12:15', '14:45', '17:15', '19:45'],
            ],
            3 => [
                1 => ['05:15', '06:45', '08:15', '09:45', '11:15', '12:45', '14:15', '15:45', '17:15', '18:45', '20:15'],
                2 => ['06:30', '08:30', '10:30', '12:30', '14:30', '16:30', '18:30', '20:30'],
                3 => ['07:30', '10:00', '12:30', '15:00', '17:30', '20:00'],
            ],
        ];

        $travelTimeBetweenStops = [
            1 => 3,
            2 => 4,
            3 => 3,
        ];

        foreach ($trips as $trip) {
            $line = $trip->line;
            $stops = $line->stops()->orderBy('sequence')->get();

            if ($stops->isEmpty()) {
                continue;
            }

            $dailyStartTimes = $startTimes[$line->line_id][$trip->day_id] ?? [];

            foreach ($dailyStartTimes as $startTime) {
                $baseMinutes = $this->timeToMinutes($startTime);

                foreach ($stops as $index => $stop) {
                    $minutesToAdd = $index * $travelTimeBetweenStops[$line->line_id];
                    $departureMinutes = $baseMinutes + $minutesToAdd;

                    $departureTimes[] = [
                        'trip_id' => $trip->trip_id,
                        'stop_id' => $stop->stop_id,
                        'departure_time' => $this->minutesToTime($departureMinutes),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        DB::table('departure_times')->insert($departureTimes);
    }

    private function timeToMinutes($time)
    {
        list($hours, $minutes) = explode(':', $time);
        return ($hours * 60) + $minutes;
    }

    private function minutesToTime($minutes)
    {
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        return sprintf('%02d:%02d', $hours, $mins);
    }
}
