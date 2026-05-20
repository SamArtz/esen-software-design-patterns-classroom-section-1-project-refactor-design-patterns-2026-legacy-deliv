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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            // SMELL: precio como float, no decimal ni integer-centavos. Errores de redondeo posibles.
            $table->float('price');
            $table->string('image')->nullable();
            // SMELL: available y stock pueden desincronizarse. No hay constraint que los mantenga consistentes.
            $table->boolean('available')->default(true);
            $table->integer('stock')->default(0);
            $table->integer('preparation_time_minutes')->default(15);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
