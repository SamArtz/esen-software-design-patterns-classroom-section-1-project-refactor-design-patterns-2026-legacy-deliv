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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('provider_id')->constrained('payment_providers');
            // SMELL: amount como float (debería ser decimal o integer en centavos)
            $table->float('amount');
            $table->string('currency')->default('USD');
            // SMELL: status como string
            $table->string('status')->default('pending');
            // 'pending','processing','completed','failed','refunded'
            $table->string('external_transaction_id')->nullable();
            // SMELL: raw_response como JSON sin tipar
            $table->json('raw_response')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
