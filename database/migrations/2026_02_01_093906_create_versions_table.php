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
        Schema::create('versions', function (Blueprint $table) { // 👈 'versions' en inglés
            $table->id();
            $table->string('version');
            $table->string('nombre')->nullable();
            $table->text('descripcion')->nullable();
            $table->timestamp('fecha_publicacion');
            $table->boolean('activo')->default(false);
            $table->timestamps();
            
            $table->unique(['activo'], 'unique_active_version');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('versiones');
    }
};
