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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('courier_id')->nullable()->constrained()->nullOnDelete();
            // SMELL: status como string sin cast a enum. Transiciones sin validar en BD.
            $table->string('status')->default('created');
            // 'created','paid','accepted','preparing','ready','picked_up','delivered','cancelled','refunded'
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('discount_total', 10, 2)->default(0);
            $table->decimal('delivery_fee', 10, 2)->default(2.50);
            $table->decimal('total', 10, 2)->default(0);
            // SMELL: delivery_address duplicada desde Customer (no normalizado)
            $table->string('delivery_address');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes(); // Order y Payment son las únicas con soft delete
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
