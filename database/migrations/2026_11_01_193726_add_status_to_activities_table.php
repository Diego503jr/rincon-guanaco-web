<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        // Estado para el eliminado lógico (igual que destinos y alojamientos)
        Schema::table('activities', function (Blueprint $table) {
            $table->enum('status', ['activo', 'inactivo'])->default('activo')->after('duracion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};