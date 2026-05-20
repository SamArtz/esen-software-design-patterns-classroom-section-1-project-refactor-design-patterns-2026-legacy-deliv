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
        Schema::create('couriers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // SMELL: vehicle_type como string, no enum
            $table->string('vehicle_type'); // 'motorcycle','bicycle','car'
            $table->string('license_plate')->nullable();
            // SMELL: posición actual sin history, sin eventos, UPDATE directo
            $table->decimal('current_latitude', 10, 7)->nullable();
            $table->decimal('current_longitude', 10, 7)->nullable();
            // SMELL: 'available' como boolean simple, sin estados intermedios
            $table->boolean('available')->default(false);
            $table->decimal('rating', 3, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('couriers');
    }
};
