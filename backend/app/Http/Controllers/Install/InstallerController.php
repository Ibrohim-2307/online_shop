<?php

namespace App\Http\Controllers\Install;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class InstallerController extends Controller
{
    protected string $lockFile;

    public function __construct()
    {
        $this->lockFile = storage_path('installed.lock');
    }

    /**
     * Show installation wizard page.
     */
    public function show(Request $request)
    {
        if (File::exists($this->lockFile)) {
            return response()->view('install.locked', [], 403);
        }

        $systemChecks = [
            'php_version' => [
                'name' => 'PHP versiyasi >= 8.4',
                'passed' => version_compare(PHP_VERSION, '8.4.0', '>='),
                'current' => PHP_VERSION,
            ],
            'ext_pdo_mysql' => [
                'name' => 'PDO MySQL kengaytmasi',
                'passed' => extension_loaded('pdo_mysql'),
            ],
            'ext_bcmath' => [
                'name' => 'BCMath (moliyaviy aniqlik uchun)',
                'passed' => extension_loaded('bcmath'),
            ],
            'ext_mbstring' => [
                'name' => 'Mbstring kengaytmasi',
                'passed' => extension_loaded('mbstring'),
            ],
            'ext_openssl' => [
                'name' => 'OpenSSL kengaytmasi',
                'passed' => extension_loaded('openssl'),
            ],
            'dir_storage' => [
                'name' => 'storage/ papkasi yozish ruxsati',
                'passed' => is_writable(storage_path()),
            ],
            'dir_bootstrap' => [
                'name' => 'bootstrap/cache papkasi yozish ruxsati',
                'passed' => is_writable(base_path('bootstrap/cache')),
            ],
        ];

        $allPassed = !in_array(false, array_column($systemChecks, 'passed'), true);

        return view('install.index', compact('systemChecks', 'allPassed'));
    }

    /**
     * Process installation and create admin user.
     */
    public function install(Request $request)
    {
        if (File::exists($this->lockFile)) {
            return response()->json([
                'success' => false,
                'message' => 'Loyiha allaqachon o\'rnatilgan. Qayta o\'rnatish qat\'iyan taqiqlangan.',
            ], 403);
        }

        // HTTPS enforcement in production
        if (config('app.env') === 'production' && !$request->secure()) {
            return response()->json([
                'success' => false,
                'message' => 'Xavfsizlik talabi: O\'rnatish faqat HTTPS protokoli orqali bajarilishi shart.',
            ], 403);
        }

        // Token enforcement
        $configuredToken = config('app.install_token', env('INSTALL_TOKEN'));
        if (empty($configuredToken)) {
            return response()->json([
                'success' => false,
                'message' => 'Xavfsizlik sababli .env faylida INSTALL_TOKEN belgilanmagan. CLI orqali "php artisan app:install" buyrug\'ini ishlating.',
            ], 403);
        }

        $validated = $request->validate([
            'install_token' => ['required', 'string'],
            'admin_name' => ['required', 'string', 'max:100'],
            'admin_email' => ['required', 'email', 'max:150'],
            'admin_password' => ['required', 'string', 'min:8'],
            'seed_demo' => ['nullable', 'boolean'],
        ]);

        if (!hash_equals($configuredToken, $validated['install_token'])) {
            return response()->json([
                'success' => false,
                'message' => 'O\'rnatish xavfsizlik tokeni (INSTALL_TOKEN) noto\'g\'ri!',
            ], 403);
        }

        // 1. Generate APP_KEY if empty
        if (empty(config('app.key'))) {
            Artisan::call('key:generate', ['--force' => true]);
        }

        // 2. Run Migrations
        Artisan::call('migrate', ['--force' => true]);

        // 3. Seed Roles & Permissions
        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\RoleAndPermissionSeeder',
            '--force' => true,
        ]);

        // 4. Optionally seed demo catalog
        if (!empty($validated['seed_demo'])) {
            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\DatabaseSeeder',
                '--force' => true,
            ]);
        }

        // 5. Create Super Admin User
        $admin = User::updateOrCreate(
            ['email' => $validated['admin_email']],
            [
                'name' => $validated['admin_name'],
                'password' => Hash::make($validated['admin_password']),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('admin');

        // 6. Lock Installation permanently
        File::put($this->lockFile, json_encode([
            'installed_at' => now()->toIso8601String(),
            'app_version' => '1.0.0',
            'php_version' => PHP_VERSION,
            'installer' => 'web',
            'installed_by_ip' => $request->ip(),
        ], JSON_PRETTY_PRINT));

        return response()->json([
            'success' => true,
            'message' => 'Bozor tizimi muvaffaqiyatli o\'rnatildi va o\'rnatuvchi bloklandi.',
            'redirect' => route('login'),
        ]);
    }
}
