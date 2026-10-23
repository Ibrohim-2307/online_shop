<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('categories')) {
            return;
        }

        $icons = [
            'qurilish-materiallari' => '🧱',
            'sanoat-uskunalari' => '🏭',
            'oziq-ovqat-va-ichimliklar' => '🌾',
            'elektr-jihozlari' => '💡',
            'boyoqlar-va-kimyo' => '🎨',
            'santexnika-va-quvurlar' => '🚰',
            'asbob-uskunalar' => '🛠️',
        ];

        foreach ($icons as $slug => $icon) {
            DB::table('categories')
                ->where('slug', $slug)
                ->where(function ($query) {
                    $query->whereNull('icon')->orWhere('icon', '');
                })
                ->update(['icon' => $icon]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe no-op
    }
};
