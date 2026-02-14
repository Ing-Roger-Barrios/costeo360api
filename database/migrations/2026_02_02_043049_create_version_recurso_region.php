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
        Schema::create('version_recurso_region', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('version_id');
            $table->unsignedBigInteger('obra_recurso_maestro_id');
            $table->unsignedBigInteger('region_id');

            $table->foreign('version_id')
                ->references('id')
                ->on('versions')
                ->onDelete('cascade');

            $table->foreign('obra_recurso_maestro_id')
                ->references('id')
                ->on('obra_recursos_maestros')
                ->onDelete('cascade');

            $table->foreign('region_id')
                ->references('id')
                ->on('regiones')
                ->onDelete('cascade');

            $table->decimal('precio_version_regional', 12, 2);

            $table->unique(
                ['version_id', 'obra_recurso_maestro_id', 'region_id'],
                'vrr_unique'
            );

            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('version_recurso_region');
    }
};
