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
        // 1. Payments Table
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('payment_method', 50)->default('demo'); // demo, payme_sandbox, click_sandbox, uzum_sandbox
            $table->string('transaction_id', 100)->nullable()->unique();
            $table->unsignedBigInteger('amount');
            $table->string('currency', 3)->default('UZS');
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded', 'partially_refunded', 'cancelled'])->default('pending');
            $table->json('payload_snapshot')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'status']);
        });

        // 2. Webhook Events (Idempotent Webhook Processing)
        Schema::create('webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 50);
            $table->string('event_id', 120)->unique();
            $table->string('signature', 255)->nullable();
            $table->json('payload');
            $table->enum('status', ['received', 'processed', 'failed', 'ignored'])->default('received');
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['provider', 'status']);
        });

        // 3. Seller Ledgers (Strictly Append-Only / Immutable Double-Entry Ledger)
        Schema::create('seller_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('sellers')->cascadeOnDelete();
            $table->foreignId('suborder_id')->nullable()->constrained('suborders')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->enum('type', ['credit', 'debit']);
            $table->enum('entry_type', [
                'order_sale',
                'commission_fee',
                'payout',
                'refund_deduction',
                'dispute_hold',
                'dispute_release'
            ]);
            $table->unsignedBigInteger('amount');
            $table->string('currency', 3)->default('UZS');
            $table->bigInteger('running_balance');
            $table->string('reference', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['seller_id', 'created_at']);
            $table->index(['suborder_id', 'entry_type']);
        });

        // 4. Refunds Table
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('suborder_id')->nullable()->constrained('suborders')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('refund_number', 45)->unique();
            $table->unsignedBigInteger('amount');
            $table->text('reason');
            $table->enum('status', ['requested', 'approved', 'rejected', 'goods_returned', 'completed'])->default('requested');
            $table->foreignId('moderator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('moderator_notes')->nullable();
            $table->boolean('is_inventory_restocked')->default(false);
            $table->timestamps();

            $table->index(['order_id', 'status']);
            $table->index(['suborder_id', 'status']);
        });

        // 5. Seller Payouts Table
        Schema::create('seller_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('sellers')->cascadeOnDelete();
            $table->unsignedBigInteger('amount');
            $table->enum('status', ['requested', 'held', 'processing', 'completed', 'rejected'])->default('requested');
            $table->timestamp('hold_until')->nullable();
            $table->boolean('has_dispute_block')->default(false);
            $table->json('bank_details')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['seller_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seller_payouts');
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('seller_ledgers');
        Schema::dropIfExists('webhook_events');
        Schema::dropIfExists('payments');
    }
};
