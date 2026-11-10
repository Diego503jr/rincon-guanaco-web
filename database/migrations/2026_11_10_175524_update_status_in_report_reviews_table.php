<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
    {
        // 1. PRIMERO: Ampliamos el ENUM para que acepte tanto los valores viejos como los nuevos temporalmente
        Schema::table('report_reviews', function (Blueprint $table) {
            $table->enum('status', ['activo', 'inactivo', 'pendiente', 'descartado', 'confirmado'])->default('activo')->change();
        });

        // 2. SEGUNDO: Ahora sí, actualizamos los datos sin riesgo de error de truncamiento
        DB::table('report_reviews')->where('status', 'activo')->update(['status' => 'pendiente']);
        DB::table('report_reviews')->where('status', 'inactivo')->update(['status' => 'descartado']);

        // 3. TERCERO: Dejamos la columna únicamente con los estados definitivos que quieres
        Schema::table('report_reviews', function (Blueprint $table) {
            $table->enum('status', ['pendiente', 'descartado', 'confirmado'])->default('pendiente')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
   public function down(): void
    {
        // Revertir en orden inverso si se hace rollback
        Schema::table('report_reviews', function (Blueprint $table) {
            $table->enum('status', ['activo', 'inactivo', 'pendiente', 'descartado', 'confirmado'])->default('activo')->change();
        });

        DB::table('report_reviews')->where('status', 'pendiente')->update(['status' => 'activo']);
        DB::table('report_reviews')->where('status', 'descartado')->update(['status' => 'inactivo']);
        DB::table('report_reviews')->where('status', 'confirmado')->update(['status' => 'inactivo']);

        Schema::table('report_reviews', function (Blueprint $table) {
            $table->enum('status', ['activo', 'inactivo'])->default('activo')->change();
        });
    }
};
