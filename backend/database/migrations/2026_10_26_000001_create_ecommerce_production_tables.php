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
        // 1. Wishlists / Favorites table (persisted per user)
        if (!Schema::hasTable('wishlists')) {
            Schema::create('wishlists', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['user_id', 'product_id'], 'unique_user_product_wishlist');
                $table->index('user_id');
            });
        }

        // 2. Click Transactions table
        if (!Schema::hasTable('click_transactions')) {
            Schema::create('click_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
                $table->string('click_trans_id', 60)->index();
                $table->string('service_id', 60);
                $table->string('click_paydoc_id', 60)->nullable();
                $table->string('merchant_trans_id', 60);
                $table->unsignedBigInteger('amount'); // UZS
                $table->integer('action')->default(0); // 0: Prepare, 1: Complete
                $table->integer('error')->default(0);
                $table->string('error_note', 255)->nullable();
                $table->string('sign_time', 40);
                $table->enum('status', ['prepared', 'paid', 'failed', 'cancelled'])->default('prepared');
                $table->timestamps();

                $table->index(['order_id', 'status']);
            });
        }

        // 3. Payme Transactions table
        if (!Schema::hasTable('payme_transactions')) {
            Schema::create('payme_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
                $table->string('payme_trans_id', 60)->unique();
                $table->unsignedBigInteger('amount'); // Tiyin
                $table->integer('state')->default(1); // 1: created, 2: completed, -1: cancelled, -2: cancelled after completion
                $table->integer('reason')->nullable();
                $table->bigInteger('payme_time');
                $table->bigInteger('create_time');
                $table->bigInteger('perform_time')->nullable();
                $table->bigInteger('cancel_time')->nullable();
                $table->timestamps();

                $table->index(['order_id', 'state']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payme_transactions');
        Schema::dropIfExists('click_transactions');
        Schema::dropIfExists('wishlists');
    }
};
