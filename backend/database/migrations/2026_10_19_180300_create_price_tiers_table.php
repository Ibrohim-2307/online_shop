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
        Schema::create('price_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->decimal('min_quantity', 12, 3);
            $table->decimal('max_quantity', 12, 3)->nullable(); // null represents infinity upper bound
            $table->unsignedBigInteger('unit_price'); // Stored as integer (minor unit / exact amount without floats)
            $table->string('currency', 3)->default('UZS');
            $table->timestamps();

            $table->index(['product_variant_id', 'min_quantity', 'max_quantity']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_tiers');
    }
};
