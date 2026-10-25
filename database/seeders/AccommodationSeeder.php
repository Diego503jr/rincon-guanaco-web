<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccommodationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        DB::table('accommodations')->insert([
            ['id' => 1, 'destination_id' => 1, 'name' => 'Hostal Montaña Verde', 'type' => 'hostal', 'address' => 'Km 11, carretera a Cerro Verde', 'price' => 20.00, 'image_url' => null, 'contact_phone' => '2400-0001', 'email' => 'montana@test.com', 'status' => 'activo', 'check_in' => '14:00:00', 'check_out' => '11:00:00', 'reserva' => null, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'destination_id' => 3, 'name' => 'Hotel Casa Izalco', 'type' => 'hotel', 'address' => 'Centro de Izalco', 'price' => 45.00, 'image_url' => null, 'contact_phone' => '2400-0002', 'email' => 'casaizalco@test.com', 'status' => 'activo', 'check_in' => '15:00:00', 'check_out' => '12:00:00', 'reserva' => null, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'destination_id' => 5, 'name' => 'Apartamento Vista San Jacinto', 'type' => 'apartamento', 'address' => 'Colonia San Jacinto', 'price' => 60.00, 'image_url' => null, 'contact_phone' => null, 'email' => null, 'status' => 'activo', 'check_in' => '14:00:00', 'check_out' => '11:00:00', 'reserva' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
