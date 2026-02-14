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
        Schema::create('obra_item_recursos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('obra_item_id')->constrained('obra_items')->onDelete('cascade');
            $table->foreignId('obra_recurso_maestro_id')->constrained('obra_recursos_maestros')->onDelete('cascade');
            $table->decimal('rendimiento', 12, 6); // Rendimiento por unidad de item
            $table->unique(['obra_item_id', 'obra_recurso_maestro_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('obra_item_recursos');
    }
};
