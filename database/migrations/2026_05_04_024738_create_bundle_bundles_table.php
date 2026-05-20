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
        Schema::create('bundle_bundles', function (Blueprint $table) {
            $table->foreignId('parent_bundle_id')->constrained('product_bundles')->cascadeOnDelete();
            $table->foreignId('child_bundle_id')->constrained('product_bundles')->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->primary(['parent_bundle_id', 'child_bundle_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bundle_bundles');
    }
};
