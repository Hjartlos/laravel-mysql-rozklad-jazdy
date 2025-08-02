<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StopsSeeder extends Seeder
{
    public function run()
    {
            DB::table('stops')->insert([
                ['stop_id' => 1, 'stop_name' => '9 Dyw. Piechoty 01', 'location_lat' => 50.012814, 'location_lon' => 21.961578, 'created_at' => now(), 'updated_at' => now()],
                ['stop_id' => 2, 'stop_name' => 'Architektów 02', 'location_lat' => 50.017722, 'location_lon' => 21.971797, 'created_at' => now(), 'updated_at' => now()],
                ['stop_id' => 3, 'stop_name' => 'Armii Krajowej kościół 09', 'location_lat' => 50.036517, 'location_lon' => 22.032503, 'created_at' => now(), 'updated_at' => now()],
                ['stop_id' => 4, 'stop_name' => 'Bat. Chłopskich / Podkarpacka 01', 'location_lat' => 50.023292, 'location_lon' => 21.980975, 'created_at' => now(), 'updated_at' => now()],
                ['stop_id' => 5, 'stop_name' => 'Beskidzka / Karkonoska 03', 'location_lat' => 50.008153, 'location_lon' => 21.949914, 'created_at' => now(), 'updated_at' => now()],
                ['stop_id' => 6, 'stop_name' => 'bł. Karoliny / Sanocka 03', 'location_lat' => 50.039467, 'location_lon' => 21.961403, 'created_at' => now(), 'updated_at' => now()],
                ['stop_id' => 7, 'stop_name' => 'Boh. X Sudeckiej Dywizji Piechoty 01', 'location_lat' => 50.031856, 'location_lon' => 22.048786, 'created_at' => now(), 'updated_at' => now()],
                ['stop_id' => 8, 'stop_name' => 'Budziwojska / Jana Pawła II 07', 'location_lat' => 49.966136, 'location_lon' => 21.995333, 'created_at' => now(), 'updated_at' => now()],
                ['stop_id' => 9, 'stop_name' => 'Chmaja / Langiewicza 01', 'location_lat' => 50.250153, 'location_lon' => 21.987625, 'created_at' => now(), 'updated_at' => now()],
                ['stop_id' => 10, 'stop_name' => 'Cienista cmentarz 01', 'location_lat' => 50.369411, 'location_lon' => 22.400375, 'created_at' => now(), 'updated_at' => now()],
                ['stop_id' => 11, 'stop_name' => 'Dąbrowskiego kościół 05', 'location_lat' => 50.024647, 'location_lon' => 21.988425, 'created_at' => now(), 'updated_at' => now()],
                ['stop_id' => 12, 'stop_name' => 'Dębicka / Krakowska 01', 'location_lat' => 50.031417, 'location_lon' => 21.966386, 'created_at' => now(), 'updated_at' => now()],
                ['stop_id' => 13, 'stop_name' => 'Jana Pawła II / Herbowa 09', 'location_lat' => 49.978461, 'location_lon' => 21.992592, 'created_at' => now(), 'updated_at' => now()],
                ['stop_id' => 14, 'stop_name' => 'Jarowa / Starowiejska 03', 'location_lat' => 50.016669, 'location_lon' => 21.968194, 'created_at' => now(), 'updated_at' => now()],
                ['stop_id' => 15, 'stop_name' => 'Kard. K. Wojtyły kościół 05', 'location_lat' => 49.992572, 'location_lon' => 22.009878, 'created_at' => now(), 'updated_at' => now()],
                ['stop_id' => 16, 'stop_name' => 'Karkonoska / Beskidzka 01', 'location_lat' => 50.008943, 'location_lon' => 21.949914, 'created_at' => now(), 'updated_at' => now()],
                ['stop_id' => 17, 'stop_name' => 'Kiepury 01', 'location_lat' => 50.023981, 'location_lon' => 22.026683, 'created_at' => now(), 'updated_at' => now()],
                ['stop_id' => 18, 'stop_name' => 'Konf. Barskich / Traugutta 01', 'location_lat' => 50.225833, 'location_lon' => 22.031108, 'created_at' => now(), 'updated_at' => now()],
                ['stop_id' => 19, 'stop_name' => 'Kopisto Uniwersytet 01', 'location_lat' => 50.039400, 'location_lon' => 22.027775, 'created_at' => now(), 'updated_at' => now()],
                ['stop_id' => 20, 'stop_name' => 'Krakowska cmentarz 09', 'location_lat' => 50.043894, 'location_lon' => 21.961200, 'created_at' => now(), 'updated_at' => now()],
                ['stop_id' => 21, 'stop_name' => 'Krzyżanowskiego 01', 'location_lat' => 50.031256, 'location_lon' => 22.023408, 'created_at' => now(), 'updated_at' => now()],
                ['stop_id' => 22, 'stop_name' => 'Księżycowa / Zajęska 01', 'location_lat' => 50.303275, 'location_lon' => 22.019750, 'created_at' => now(), 'updated_at' => now()],
                ['stop_id' => 23, 'stop_name' => 'Kwiatkowskiego Dom Studenta 01', 'location_lat' => 50.028439, 'location_lon' => 22.016669, 'created_at' => now(), 'updated_at' => now()],
                ['stop_id' => 24, 'stop_name' => 'Langiewicza / Hoffmanowej 01', 'location_lat' => 50.033128, 'location_lon' => 21.994661, 'created_at' => now(), 'updated_at' => now()],
                ['stop_id' => 25, 'stop_name' => 'Leszka Czarnego 01', 'location_lat' => 50.027731, 'location_lon' => 22.047933, 'created_at' => now(), 'updated_at' => now()],
                ['stop_id' => 26, 'stop_name' => 'Lubelska / Wyzwolenia 03', 'location_lat' => 50.032686, 'location_lon' => 22.015292, 'created_at' => now(), 'updated_at' => now()],
                ['stop_id' => 27, 'stop_name' => 'Lwowska cmentarz 03', 'location_lat' => 50.254250, 'location_lon' => 22.032161, 'created_at' => now(), 'updated_at' => now()],
                ['stop_id' => 28, 'stop_name' => 'Łukasiewicza / Krokusowa 01', 'location_lat' => 50.009122, 'location_lon' => 22.033014, 'created_at' => now(), 'updated_at' => now()],
                ['stop_id' => 29, 'stop_name' => 'Marszałkowska rondo 03', 'location_lat' => 50.249381, 'location_lon' => 21.999336, 'created_at' => now(), 'updated_at' => now()],
                ['stop_id' => 30, 'stop_name' => 'Mieszka I / Kustronia 01', 'location_lat' => 50.036703, 'location_lon' => 22.038028, 'created_at' => now(), 'updated_at' => now()]
            ]);
    }
}
