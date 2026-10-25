<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        DB::table('reviews')->insert([
            ['id' => 1, 'user_id' => 2, 'destination_id' => 1, 'rating' => 5, 'comment' => 'Hermosa vista y buen clima.', 'travel_date' => '2026-08-15', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'user_id' => 3, 'destination_id' => 1, 'rating' => 4, 'comment' => 'Ideal para ir en familia.', 'travel_date' => '2026-09-02', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'user_id' => 4, 'destination_id' => 4, 'rating' => 5, 'comment' => 'La comida estuvo deliciosa.', 'travel_date' => '2026-09-20', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'user_id' => 2, 'destination_id' => 5, 'rating' => 3, 'comment' => null, 'travel_date' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
