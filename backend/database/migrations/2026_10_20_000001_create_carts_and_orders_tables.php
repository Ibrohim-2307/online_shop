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
        // 1. Carts & Cart Items
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('session_id', 100)->nullable()->index();
            $table->enum('status', ['active', 'converted', 'abandoned'])->default('active');
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained('carts')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->decimal('quantity', 12, 3)->default(1.000);
            $table->timestamps();

            $table->unique(['cart_id', 'product_variant_id'], 'unique_cart_variant');
        });

        // 2. Parent Orders
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 40)->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['pending_payment', 'processing', 'completed', 'cancelled'])->default('pending_payment');
            $table->string('currency', 3)->default('UZS');
            $table->unsignedBigInteger('items_total');
            $table->unsignedBigInteger('shipping_total')->default(0);
            $table->unsignedBigInteger('grand_total');
            $table->json('shipping_address');
            $table->string('idempotency_key', 100)->nullable()->index();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        // 3. Seller Suborders (Multi-vendor split)
        Schema::create('suborders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('sellers')->cascadeOnDelete();
            $table->string('suborder_number', 45)->unique();
            $table->enum('status', ['pending', 'confirmed', 'preparing', 'shipped', 'delivered', 'cancelled'])->default('pending');
            $table->unsignedBigInteger('items_subtotal');
            $table->unsignedBigInteger('shipping_cost')->default(0);
            $table->unsignedBigInteger('total_amount');
            $table->decimal('commission_rate', 5, 2)->default(0.00);
            $table->unsignedBigInteger('commission_amount')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['seller_id', 'status']);
            $table->index(['order_id', 'seller_id']);
        });

        // 4. Order Items (Snapshot)
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('suborder_id')->constrained('suborders')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained('product_variants')->restrictOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->string('product_name');
            $table->string('variant_sku');
            $table->string('unit', 20);
            $table->decimal('quantity', 12, 3);
            $table->unsignedBigInteger('unit_price');
            $table->unsignedBigInteger('subtotal');
            $table->unsignedBigInteger('price_tier_id')->nullable();
            $table->json('attributes_snapshot')->nullable();
            $table->timestamps();

            $table->index(['suborder_id', 'product_variant_id']);
        });

        // 5. Shipments
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('suborder_id')->constrained('suborders')->cascadeOnDelete();
            $table->string('tracking_number', 50)->unique();
            $table->foreignId('courier_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['pending', 'assigned', 'picked_up', 'in_transit', 'delivered', 'failed'])->default('pending');
            $table->string('shipping_method', 50)->default('standard');
            $table->unsignedBigInteger('shipping_cost')->default(0);
            $table->json('pickup_address')->nullable();
            $table->json('delivery_address')->nullable();
            $table->timestamp('estimated_delivery_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->index(['courier_id', 'status']);
        });

        // 6. Inventory Reservations (with TTL)
        Schema::create('inventory_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->decimal('quantity', 12, 3);
            $table->enum('status', ['reserved', 'committed', 'released', 'expired'])->default('reserved');
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index(['status', 'expires_at'], 'idx_reservations_status_expiry');
            $table->index(['order_id', 'status']);
        });

        // 7. Idempotency Keys
        Schema::create('idempotency_keys', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('request_hash', 64);
            $table->integer('response_code');
            $table->json('response_body');
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index(['key', 'user_id']);
            $table->index('expires_at');
        });

        // 8. Outbox Events (Transactional Outbox Pattern)
        Schema::create('outbox_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_type', 100);
            $table->string('aggregate_type', 100);
            $table->unsignedBigInteger('aggregate_id');
            $table->json('payload');
            $table->enum('status', ['pending', 'published', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at'], 'idx_outbox_status_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outbox_events');
        Schema::dropIfExists('idempotency_keys');
        Schema::dropIfExists('inventory_reservations');
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('suborders');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
    }
};
