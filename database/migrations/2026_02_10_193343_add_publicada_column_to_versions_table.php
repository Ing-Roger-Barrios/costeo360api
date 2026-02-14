<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('versions', function (Blueprint $table) {
            // Añadir columna publicada
            $table->boolean('publicada')->default(false)->after('activo');
            
            // Añadir índice para mejorar el rendimiento
            $table->index('publicada');
        });
        
        // Establecer la versión más reciente como publicada (si existe)
        DB::statement("
            UPDATE versions 
            SET publicada = true 
            WHERE id = (
                SELECT id FROM (
                    SELECT id FROM versions 
                    ORDER BY fecha_publicacion DESC 
                    LIMIT 1
                ) AS latest
            ) AND fecha_publicacion IS NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('versions', function (Blueprint $table) {
            $table->dropIndex(['publicada']);
            $table->dropColumn('publicada');
        });
    }
};
