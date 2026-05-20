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
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('business_name');
            $table->text('description')->nullable();
            $table->string('logo')->nullable();
            $table->string('address');
            $table->string('city');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            // SMELL: JSON sin validar estructura. Formato esperado: {"monday":{"open":"08:00","close":"22:00","closed":false},...}
            $table->json('opening_hours')->nullable();
            $table->decimal('commission_rate', 5, 2)->default(15.00);
            // SMELL: status como string, no enum. Sin scope 'active'.
            $table->string('status')->default('active'); // 'active','inactive','suspended'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
