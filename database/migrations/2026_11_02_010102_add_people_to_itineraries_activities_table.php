<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Personas que harán la actividad dentro del itinerario (modal "Detalles de Reserva")
        Schema::table('itineraries_activities', function (Blueprint $table) {
            $table->unsignedTinyInteger('people')->default(1)->after('title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('itineraries_activities', function (Blueprint $table) {
            $table->dropColumn('people');
        });
    }
};