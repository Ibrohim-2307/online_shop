<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seller_payouts', function (Blueprint $table) {
            // The admin's answer is kept apart from the seller's own note
            $table->text('admin_notes')->nullable()->after('notes');
            $table->foreignId('reviewed_by')->nullable()->after('admin_notes')->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
        });
    }

    public function down(): void
    {
        Schema::table('seller_payouts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn(['admin_notes', 'reviewed_at']);
        });
    }
};
