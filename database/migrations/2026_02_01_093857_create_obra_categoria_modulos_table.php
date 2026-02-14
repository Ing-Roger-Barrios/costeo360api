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
        Schema::create('obra_categoria_modulos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('obra_categoria_id')->constrained('obra_categorias')->onDelete('cascade');
            $table->foreignId('obra_modulo_id')->constrained('obra_modulos')->onDelete('cascade');
            $table->integer('orden')->default(0); // Para reordenamiento
            $table->unique(['obra_categoria_id', 'obra_modulo_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('obra_categoria_modulos');
    }
};
