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
        Schema::create('obra_modulo_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('obra_modulo_id')->constrained('obra_modulos')->onDelete('cascade');
            $table->foreignId('obra_item_id')->constrained('obra_items')->onDelete('cascade');
            $table->integer('orden')->default(0); // Para reordenamiento
            $table->unique(['obra_modulo_id', 'obra_item_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('obra_modulo_items');
    }
};
