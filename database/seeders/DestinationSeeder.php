<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DestinationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        DB::table('destinations')->insert([
            ['id' => 1, 'name' => 'Parque Nacional Cerro Verde', 'category' => 'montaña', 'traveler_type' => 'familia', 'description' => 'Parque con senderos y vista al volcán de Izalco.', 'department' => 'Sonsonate', 'municipality' => 'Izalco', 'image_url' => null, 'latitude' => 13.8267000, 'longitude' => -89.6333000, 'status' => 'activo', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'name' => 'Volcán de Izalco', 'category' => 'aventura', 'traveler_type' => 'mochilero', 'description' => 'Ascenso guiado al volcán.', 'department' => 'Sonsonate', 'municipality' => 'Izalco', 'image_url' => null, 'latitude' => 13.8147000, 'longitude' => -89.6331000, 'status' => 'activo', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'name' => 'Centro histórico de Izalco', 'category' => 'cultural', 'traveler_type' => 'pareja', 'description' => 'Plaza, iglesia y tradiciones de la ciudad.', 'department' => 'Sonsonate', 'municipality' => 'Izalco', 'image_url' => null, 'latitude' => 13.7411000, 'longitude' => -89.6692000, 'status' => 'activo', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'name' => 'Ruta gastronómica de Izalco', 'category' => 'gastronomico', 'traveler_type' => null, 'description' => 'Comida típica salvadoreña local.', 'department' => 'Sonsonate', 'municipality' => 'Izalco', 'image_url' => null, 'latitude' => 13.7425000, 'longitude' => -89.6680000, 'status' => 'activo', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'name' => 'Cerro San Jacinto', 'category' => 'relax', 'traveler_type' => 'pareja', 'description' => 'Mirador con vista panorámica de San Salvador.', 'department' => 'San Salvador', 'municipality' => 'San Jacinto', 'image_url' => null, 'latitude' => 13.6776000, 'longitude' => -89.1809000, 'status' => 'activo', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
