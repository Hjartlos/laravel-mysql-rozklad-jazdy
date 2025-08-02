<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LinesSeeder extends Seeder
{
    public function run()
    {
        DB::table('lines')->insert([
            [
                'line_id' => 1,
                'line_number' => '101',
                'line_name' => '9 Dyw. Piechoty - Jana Pawła II',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'line_id' => 2,
                'line_number' => '202',
                'line_name' => 'Beskidzka - Sudecka',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'line_id' => 3,
                'line_number' => '303',
                'line_name' => 'Dąbrowskiego - Podkarpacka (okrężna)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
