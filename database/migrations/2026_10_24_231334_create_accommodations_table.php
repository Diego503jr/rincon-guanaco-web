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
        Schema::create('accommodations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('destination_id')->constrained()->cascadeOnDelete();
            $table->string('name', 150);
            $table->enum('type', ['hotel', 'apartamento', 'hostal']);
            $table->string('address', 255)->nullable();
            $table->decimal('price', 10, 2);
            $table->text('image_url')->nullable();
            $table->string('contact_phone', 50)->nullable();
            $table->string('email', 150)->nullable();
            $table->enum('status', ['activo', 'inactivo'])->default('activo');
            $table->time('check_in');
            $table->time('check_out');
            $table->integer('reserva')->nullable(); // ver nota arriba
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accommodations');
    }
};
