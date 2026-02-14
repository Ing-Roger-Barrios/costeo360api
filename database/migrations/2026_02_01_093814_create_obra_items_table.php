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
        Schema::create('obra_items', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique(); // Ej: CONC-001
            $table->string('descripcion'); // Ej: Concreto f'c 175 kg/cm²
            $table->string('unidad'); // Ej: 'm³'
            $table->text('notas')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('obra_items');
    }
};
