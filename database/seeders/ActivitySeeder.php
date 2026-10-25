<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        DB::table('activities')->insert([
            ['id' => 1, 'destination_id' => 1, 'activity_name' => 'Caminata por el sendero principal', 'notes' => 'Llevar agua y ropa abrigada.', 'people' => 4, 'price' => 5.00, 'duracion' => '02:00:00', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'destination_id' => 2, 'activity_name' => 'Ascenso guiado al volcán', 'notes' => 'Salida temprano.', 'people' => 2, 'price' => 15.00, 'duracion' => '05:00:00', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'destination_id' => 3, 'activity_name' => 'Recorrido cultural a pie', 'notes' => null, 'people' => 2, 'price' => 8.00, 'duracion' => '01:30:00', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'destination_id' => 4, 'activity_name' => 'Degustación de comida típica', 'notes' => 'Incluye bebida.', 'people' => 3, 'price' => 12.50, 'duracion' => '01:00:00', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'destination_id' => 5, 'activity_name' => 'Atardecer en el mirador', 'notes' => null, 'people' => 2, 'price' => 3.00, 'duracion' => '01:30:00', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
