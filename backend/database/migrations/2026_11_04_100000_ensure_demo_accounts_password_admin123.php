<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Ensures all existing demo accounts have password 'admin123'.
     */
    public function up(): void
    {
        $emails = [
            'admin@market.uz',
            'seller@market.uz',
            'customer@market.uz',
            'courier@market.uz',
        ];

        foreach ($emails as $email) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $user->update([
                    'password' => Hash::make('admin123'),
                    'is_active' => true,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
