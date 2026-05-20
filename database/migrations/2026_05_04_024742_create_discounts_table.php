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
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            // SMELL: type como string, sin enum. Switch en Discount::apply() depende de este string.
            $table->string('type'); // 'percentage','fixed_amount','bogo','first_purchase','free_delivery'
            $table->decimal('value', 10, 2);
            $table->decimal('min_order_amount', 10, 2)->nullable();
            $table->decimal('max_discount_amount', 10, 2)->nullable();
            $table->timestamp('valid_from');
            $table->timestamp('valid_to');
            $table->unsignedInteger('max_uses')->nullable();
            // SMELL: current_uses sin lock optimista. Race condition posible.
            $table->unsignedInteger('current_uses')->default(0);
            $table->foreignId('vendor_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
