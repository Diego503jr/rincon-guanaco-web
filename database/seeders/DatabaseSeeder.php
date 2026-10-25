<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            DestinationSeeder::class,
            ActivitySeeder::class,
            AccommodationSeeder::class,
            ReviewSeeder::class,
            ItinerarySeeder::class,
            ItineraryActivitySeeder::class,
            ItineraryAccommodationSeeder::class,
            FavoriteDestinationSeeder::class,
            ReportReviewSeeder::class,
        ]);
    }
}
