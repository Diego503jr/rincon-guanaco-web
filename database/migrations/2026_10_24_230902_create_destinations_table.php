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
        Schema::create('destinations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->enum('category', ['playa', 'montaña', 'cultural', 'gastronomico', 'aventura', 'relax']);
            $table->enum('traveler_type', ['mochilero', 'familia', 'pareja'])->nullable();
            $table->text('description')->nullable();
            $table->enum('department', ['Sonsonate', 'San Salvador']);
            $table->enum('municipality', ['Izalco', 'San Jacinto']);
            $table->text('image_url')->nullable();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->enum('status', ['activo', 'inactivo'])->default('activo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('destinations');
    }
};
