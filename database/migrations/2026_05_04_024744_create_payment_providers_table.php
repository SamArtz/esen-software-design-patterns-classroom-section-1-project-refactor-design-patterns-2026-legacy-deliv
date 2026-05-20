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
        Schema::create('payment_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // 'Wompi','N1co','BAC Transfer','Visa Direct'
            $table->string('api_endpoint');
            // SMELL: api_key "cifrada" pero encrypt() mal usado (solo base64 en realidad)
            $table->string('api_key');
            $table->boolean('enabled')->default(true);
            $table->unsignedInteger('priority')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_providers');
    }
};
