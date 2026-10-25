<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItineraryActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        DB::table('itineraries_activities')->insert([
            ['itinerary_id' => 1, 'activity_id' => 2, 'title' => 'Subida al volcán', 'date_time' => '2026-11-07 06:00:00', 'created_at' => $now, 'updated_at' => $now],
            ['itinerary_id' => 1, 'activity_id' => 4, 'title' => 'Almuerzo típico', 'date_time' => '2026-11-07 13:00:00', 'created_at' => $now, 'updated_at' => $now],
            ['itinerary_id' => 2, 'activity_id' => 1, 'title' => 'Caminata familiar', 'date_time' => '2026-09-02 09:00:00', 'created_at' => $now, 'updated_at' => $now],
            ['itinerary_id' => 3, 'activity_id' => 5, 'title' => 'Atardecer en pareja', 'date_time' => '2026-11-14 17:00:00', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
