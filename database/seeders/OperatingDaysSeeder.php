<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OperatingDaysSeeder extends Seeder
{
    public function run()
    {
        DB::table('operating_days')->insert([
            [
                'day_id' => 1,
                'name' => 'Dni robocze',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'day_id' => 2,
                'name' => 'Soboty',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'day_id' => 3,
                'name' => 'Niedziele i święta',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
