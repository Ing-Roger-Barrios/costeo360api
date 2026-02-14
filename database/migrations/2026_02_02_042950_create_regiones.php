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
        Schema::create('regiones', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique(); // Ej: LP, SC, CB
            $table->string('nombre'); // Ej: La Paz, Santa Cruz, Cochabamba
            $table->string('pais')->default('Bolivia'); // Para expansión futura
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regiones');
    }
};
