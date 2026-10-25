<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItineraryAccommodationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        DB::table('itinerary_accommodations')->insert([
            ['itinerary_id' => 1, 'accommodation_id' => 2, 'guests' => 1, 'total_price' => 45.00, 'status' => 'confirmada', 'check_in' => '2026-11-07 15:00:00', 'check_out' => '2026-11-08 12:00:00', 'is_completed' => false, 'created_at' => $now, 'updated_at' => $now],
            ['itinerary_id' => 2, 'accommodation_id' => 1, 'guests' => 4, 'total_price' => 20.00, 'status' => 'confirmada', 'check_in' => '2026-09-01 14:00:00', 'check_out' => '2026-09-02 11:00:00', 'is_completed' => true, 'created_at' => $now, 'updated_at' => $now],
            ['itinerary_id' => 3, 'accommodation_id' => 3, 'guests' => 2, 'total_price' => 60.00, 'status' => 'pendiente', 'check_in' => '2026-11-14 14:00:00', 'check_out' => '2026-11-15 11:00:00', 'is_completed' => false, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
