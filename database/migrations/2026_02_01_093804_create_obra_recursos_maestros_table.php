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
        Schema::create('obra_recursos_maestros', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique(); // Ej: CEM-001
            $table->string('nombre'); // Ej: Cemento tipo I
            $table->string('tipo'); // 'Material', 'Mano de Obra', 'Equipo'
            $table->string('unidad'); // Ej: 'bolsa', 'hora', 'unidad'
            $table->decimal('precio_referencia', 12, 2); // Precio unitario
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('obra_recursos_maestros');
    }
};
