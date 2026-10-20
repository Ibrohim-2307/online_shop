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
        // 1. RFQ (Request For Quotation) - Ulgurji va tonnalik buyurtmalar uchun
        Schema::create('rfqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('sellers')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->decimal('quantity', 12, 3);
            $table->string('unit', 20); // ton, kg, piece, box
            $table->unsignedBigInteger('target_price')->nullable(); // Taklif qilingan narx
            $table->enum('status', ['pending', 'quoted', 'accepted', 'rejected', 'expired'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->timestamps();

            $table->index(['seller_id', 'status']);
            $table->index(['user_id', 'status']);
        });

        // 2. RFQ Quotations (Sotuvchi kotirovka taklifi)
        Schema::create('rfq_quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rfq_id')->constrained('rfqs')->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('sellers')->cascadeOnDelete();
            $table->unsignedBigInteger('unit_price'); // Kelishilgan dona/tonna narxi
            $table->unsignedBigInteger('shipping_cost')->default(0); // Yetkazish xarajati
            $table->boolean('is_shipping_negotiated')->default(false); // Tonnalik yukda yetkazish kelishilganmi
            $table->unsignedBigInteger('total_price'); // Jami summa
            $table->enum('status', ['offered', 'accepted', 'rejected', 'expired'])->default('offered');
            $table->timestamp('expires_at');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['rfq_id', 'status']);
        });

        // 3. Marketing Banners
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('image_url');
            $table->string('link_url')->nullable();
            $table->string('position')->default('home_hero');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banners');
        Schema::dropIfExists('rfq_quotations');
        Schema::dropIfExists('rfqs');
    }
};
