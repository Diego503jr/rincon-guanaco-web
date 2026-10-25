<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItinerarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        DB::table('itineraries')->insert([
            ['id' => 1, 'user_id' => 2, 'title' => 'Fin de semana en Izalco', 'is_completed' => false, 'total_price' => 85.00, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'user_id' => 3, 'title' => 'Paseo familiar a Cerro Verde', 'is_completed' => true, 'total_price' => 40.00, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'user_id' => 4, 'title' => 'Escapada romántica', 'is_completed' => false, 'total_price' => 120.00, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
