<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReportReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        DB::table('report_reviews')->insert([
            ['user_id' => 3, 'review_id' => 4, 'nota' => 'Reseña sin contenido útil.', 'type_report' => 'indebido', 'status' => 'activo', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
