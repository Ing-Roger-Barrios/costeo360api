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
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('license_key')->unique();
            $table->enum('type', ['monthly', 'yearly', 'lifetime'])->default('monthly');
            $table->timestamp('start_date');
            $table->timestamp('end_date')->nullable(); // null para lifetime
            $table->boolean('is_active')->default(true);
            $table->boolean('is_paid')->default(false);
            $table->decimal('amount', 10, 2)->default(0.00);
            $table->string('currency')->default('BOB');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
};
