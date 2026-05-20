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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            // SMELL: polimorfismo manual con string, no morphs de Laravel
            $table->string('item_type'); // 'product' o 'bundle'
            $table->unsignedBigInteger('item_id');
            $table->unsignedInteger('quantity');
            // SMELL: unit_price como float + a veces se recalcula desde Product en lugar de usar el snapshot
            $table->float('unit_price');
            $table->float('subtotal');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
