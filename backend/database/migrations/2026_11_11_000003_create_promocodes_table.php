<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promocodes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('title')->nullable();
            $table->enum('type', ['fixed', 'percentage'])->default('fixed');
            $table->unsignedBigInteger('value'); // e.g. 50000 UZS or 10%
            $table->unsignedBigInteger('min_order_amount')->default(0);
            $table->unsignedBigInteger('max_discount_amount')->nullable();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['code', 'is_active']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('promocode', 32)->nullable()->after('currency');
            $table->unsignedBigInteger('discount_total')->default(0)->after('items_total');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['promocode', 'discount_total']);
        });
        Schema::dropIfExists('promocodes');
    }
};
