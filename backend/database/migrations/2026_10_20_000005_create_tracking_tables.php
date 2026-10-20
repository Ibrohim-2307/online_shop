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
        // 1. Courier GPS Locations (Batch tracking)
        Schema::create('courier_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('courier_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('shipment_id')->nullable()->constrained('shipments')->nullOnDelete();
            $table->string('event_id', 100)->unique(); // Client-side UUID for idempotency
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->float('accuracy')->nullable(); // Meters
            $table->float('heading')->nullable(); // 0-360 degrees
            $table->float('speed')->nullable(); // m/s or km/h
            $table->timestamp('recorded_at'); // GPS satellite timestamp on device
            $table->timestamp('received_at')->useCurrent(); // Server ingestion timestamp
            $table->timestamps();

            $table->index(['courier_id', 'recorded_at']);
            $table->index(['shipment_id', 'recorded_at']);
        });

        // 2. Shipment Timeline Events (Audited delivery lifecycle)
        Schema::create('shipment_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->cascadeOnDelete();
            $table->string('event_type', 50); // assigned, picked_up, location_update, delayed, delivered, failed
            $table->text('notes')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['shipment_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipment_events');
        Schema::dropIfExists('courier_locations');
    }
};
