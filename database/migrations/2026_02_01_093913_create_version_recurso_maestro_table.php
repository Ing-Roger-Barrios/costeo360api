<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('version_recurso_maestro', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('version_id')
                ->constrained('versions') 
                ->onDelete('cascade');
            
            // 👇 CAMBIO AQUÍ: Especifica el nombre exacto de la tabla
            $table->unsignedBigInteger('obra_recurso_maestro_id');

            $table->foreign('obra_recurso_maestro_id')
                ->references('id')
                ->on('obra_recursos_maestros')
                ->onDelete('cascade');

            $table->decimal('precio_version', 12, 2);
            $table->unique(['version_id', 'obra_recurso_maestro_id'], 'vrmaestro_unique');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('version_recurso_maestro');
    }
};