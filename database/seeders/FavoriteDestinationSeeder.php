<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FavoriteDestinationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        DB::table('favorite_destinations')->insert([
            ['user_id' => 2, 'destination_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => 2, 'destination_id' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => 3, 'destination_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => 4, 'destination_id' => 5, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
