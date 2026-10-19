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
            $table->foreignId('seller_id')->constrained('sellers')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('trade_mode', ['retail_only', 'wholesale_only', 'both'])->default('both');
            $table->enum('unit', ['piece', 'box', 'kg', 'ton'])->default('piece');
            $table->unsignedInteger('items_per_box')->nullable();
            $table->decimal('unit_step', 12, 3)->default(1.000);
            $table->decimal('min_order_quantity', 12, 3)->default(1.000);
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected'])->default('pending');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['seller_id', 'status', 'is_active']);
            $table->index(['category_id', 'status', 'is_active']);
            $table->index('trade_mode');
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
