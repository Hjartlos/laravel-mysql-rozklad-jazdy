<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LineStopSeeder extends Seeder
{
    public function run()
    {
        $lineRoutes = [
            1 => [
                1, 14, 4, 11, 24, 26, 19, 30, 21, 23, 17, 15, 13, 8
            ],
            2 => [
                5, 16, 2, 12, 6, 20, 27, 29, 18, 22, 25, 7
            ],
            3 => [
                11, 24, 21, 23, 26, 30, 3, 28, 9, 10, 1, 14, 4
            ]
        ];

        $insertData = [];

        foreach ($lineRoutes as $lineId => $stops) {
            $sequence = 1;
            foreach ($stops as $stopId) {
                $insertData[] = [
                    'line_id' => $lineId,
                    'stop_id' => $stopId,
                    'sequence' => $sequence++,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::table('linestop')->insert($insertData);
    }

}
