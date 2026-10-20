<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class InstallAppCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:install 
                            {--admin-name=Admin : Initial administrator name}
                            {--admin-email= : Initial administrator email}
                            {--admin-password= : Initial administrator password}
                            {--seed-demo : Seed demo catalog and users}
                            {--force : Force re-installation even if installed.lock exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform one-time application installation and admin account initialization';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $lockFile = storage_path('installed.lock');

        if (File::exists($lockFile) && !$this->option('force')) {
            $this->error('Loyiha allaqachon o\'rnatilgan! (storage/installed.lock mavjud).');
            $this->line('Qayta o\'rnatish uchun storage/installed.lock faylini o\'chiring yoki --force bayrog\'ini ishlating.');
            return Command::FAILURE;
        }

        $this->info('====================================================');
        $this->info('  Online Marketplace - O\'rnatish va Sozlash Jarayoni');
        $this->info('====================================================');

        // 1. Generate APP_KEY if not already set
        if (empty(config('app.key'))) {
            $this->info('1. APP_KEY yaratilmoqda...');
            Artisan::call('key:generate', ['--force' => true]);
            $this->line(Artisan::output());
        } else {
            $this->info('1. APP_KEY allaqachon mavjud.');
        }

        // 2. Run Database Migrations
        $this->info('2. Ma\'lumotlar bazasi jadvallari yaratilmoqda (migrate --force)...');
        $exitCode = Artisan::call('migrate', ['--force' => true]);
        $this->line(Artisan::output());
        if ($exitCode !== 0) {
            $this->error('Migratsiyada xatolik yuz berdi. Iltimos, ma\'lumotlar bazasi ulanishini tekshiring.');
            return Command::FAILURE;
        }

        // 3. Seed Roles and Permissions
        $this->info('3. Tizim rollari va ruxsatlari yaratilmoqda...');
        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\RoleAndPermissionSeeder',
            '--force' => true,
        ]);
        $this->line(Artisan::output());

        // 4. Optionally Seed Demo Catalog
        if ($this->option('seed-demo')) {
            $this->info('4. Demo katalog va foydalanuvchilar yuklanmoqda...');
            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\DatabaseSeeder',
                '--force' => true,
            ]);
            $this->line(Artisan::output());
        }

        // 5. Create Initial Admin User
        $adminEmail = $this->option('admin-email') ?: $this->ask('Administrator elektron pochtasini kiriting (ixtiyoriy, Enter bosing o\'tkazib yuborish uchun)');
        if (!empty($adminEmail)) {
            $adminPassword = $this->option('admin-password') ?: $this->secret('Administrator parolini kiriting (kamida 8 belgi)');
            if (empty($adminPassword) || strlen($adminPassword) < 8) {
                $this->error('Parol kamida 8 ta belgidan iborat bo\'lishi shart!');
                return Command::FAILURE;
            }

            $adminName = $this->option('admin-name') ?: 'Bosh Administrator';

            $adminUser = User::updateOrCreate(
                ['email' => $adminEmail],
                [
                    'name' => $adminName,
                    'password' => Hash::make($adminPassword),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );

            $adminUser->assignRole('admin');
            $this->info("Administrator muvaffaqiyatli yaratildi: {$adminEmail}");
        }

        // 6. Write storage/installed.lock
        $lockPayload = json_encode([
            'installed_at' => now()->toIso8601String(),
            'app_version' => '1.0.0',
            'php_version' => PHP_VERSION,
            'installer' => 'cli',
        ], JSON_PRETTY_PRINT);

        File::put($lockFile, $lockPayload);
        $this->info("5. storage/installed.lock yaratildi. Qayta o'rnatish bloklandi.");

        $this->info('====================================================');
        $this->info('  O\'rnatish muvaffaqiyatli yakunlandi!');
        $this->info('====================================================');

        return Command::SUCCESS;
    }
}
