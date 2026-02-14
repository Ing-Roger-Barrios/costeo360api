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
        Schema::table('obra_modulo_items', function (Blueprint $table) {
            $table->decimal('rendimiento', 15, 6)->nullable()->default(0.000000);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('obra_modulo_items', function (Blueprint $table) {
            $table->dropColumn('rendimiento');
        });
    }
};
