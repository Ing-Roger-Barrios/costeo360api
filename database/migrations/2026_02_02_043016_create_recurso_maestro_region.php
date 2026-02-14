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
        Schema::create('recurso_maestro_region', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('obra_recurso_maestro_id');
            $table->unsignedBigInteger('region_id');

            $table->foreign('obra_recurso_maestro_id')
                ->references('id')
                ->on('obra_recursos_maestros')
                ->onDelete('cascade');

            $table->foreign('region_id')
                ->references('id')
                ->on('regiones')
                ->onDelete('cascade');

            $table->decimal('precio_regional', 12, 2);

            $table->unique(
                ['obra_recurso_maestro_id', 'region_id'],
                'rmr_unique'
            );

            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurso_maestro_region');
    }
};
