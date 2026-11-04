<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SecureAccountsCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'app:secure-accounts';

    /**
     * @var string
     */
    protected $description = 'Set the administrator password from ADMIN_EMAIL/ADMIN_PASSWORD and lock demo accounts whose passwords are public';

    /**
     * Demo accounts created by the seeders, with the passwords published in the repository.
     */
    public const DEMO_ACCOUNTS = [
        'admin@market.uz' => 'Admin123!',
        'seller@market.uz' => 'Seller123!',
        'customer@market.uz' => 'Customer123!',
        'courier@market.uz' => 'Courier123!',
    ];

    public function handle(): int
    {
        $adminEmail = (string) (config('app.admin_email') ?: 'admin@market.uz');
        $adminPassword = (string) config('app.admin_password');

        // 1. Administrator password comes from the hosting dashboard, never from the code
        if ($adminPassword !== '') {
            if (mb_strlen($adminPassword) < 10) {
                $this->error("ADMIN_PASSWORD kamida 10 ta belgidan iborat bo'lishi kerak. Admin paroli o'zgartirilmadi.");
            } else {
                $admin = User::where('email', $adminEmail)->first();
                if (!$admin) {
                    $admin = User::create([
                        'name' => 'Administrator',
                        'email' => $adminEmail,
                        'password' => $adminPassword,
                        'is_active' => true,
                    ]);
                    $this->info("Administrator yaratildi: {$adminEmail}");
                } elseif (!Hash::check($adminPassword, $admin->password)) {
                    $admin->update(['password' => $adminPassword, 'is_active' => true]);
                    $admin->tokens()->delete();
                    $this->info("Administrator paroli ADMIN_PASSWORD bo'yicha yangilandi: {$adminEmail}");
                }
                if (!$admin->hasRole(['superadmin', 'admin'])) {
                    $admin->assignRole('superadmin');
                }
            }
        }

        // 2. Demo mode: the owner chose one shared password for all demo accounts (DEMO_PASSWORD in the dashboard)
        $demoPassword = (string) config('app.demo_password');
        if (config('app.demo_mode') && $demoPassword !== '') {
            if (mb_strlen($demoPassword) < 6) {
                $this->error("DEMO_PASSWORD kamida 6 ta belgidan iborat bo'lishi kerak. Demo parollar o'zgartirilmadi.");

                return self::SUCCESS;
            }

            foreach (array_keys(self::DEMO_ACCOUNTS) as $email) {
                // An explicit ADMIN_PASSWORD for this same account wins over the shared demo password
                if ($email === $adminEmail && $adminPassword !== '' && mb_strlen($adminPassword) >= 10) {
                    continue;
                }

                $user = User::where('email', $email)->first();
                if ($user && !Hash::check($demoPassword, $user->password)) {
                    $user->update(['password' => $demoPassword, 'is_active' => true]);
                    $user->tokens()->delete();
                    $this->info("Demo hisob paroli DEMO_PASSWORD ga o'rnatildi: {$email}");
                }
            }

            return self::SUCCESS;
        }

        // 3. On a real store, nobody may log in with a password that is printed in the repository
        if (!app()->environment('production') || config('app.demo_mode')) {
            $this->line('Demo hisoblar tegilmadi (production emas yoki APP_DEMO_MODE yoqilgan).');

            return self::SUCCESS;
        }

        foreach (self::DEMO_ACCOUNTS as $email => $publicPassword) {
            if ($email === $adminEmail && $adminPassword !== '' && mb_strlen($adminPassword) >= 10) {
                continue;
            }

            $user = User::where('email', $email)->first();
            if ($user && Hash::check($publicPassword, $user->password)) {
                $user->update(['password' => Str::password(40)]);
                $user->tokens()->delete();
                $this->warn("Demo hisob yopildi (paroli ochiq edi): {$email}");
            }
        }

        if ($adminPassword === '') {
            $this->warn("ADMIN_PASSWORD o'rnatilmagan: admin paneliga kirish uchun hosting sozlamalariga ADMIN_PASSWORD qo'shing.");
        }

        return self::SUCCESS;
    }
}
