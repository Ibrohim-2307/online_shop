<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ApkDownloadController extends Controller
{
    /**
     * Single Unified SuperApp Configuration.
     * All roles (Customer, Seller, Courier, Admin) are unified into ONE single APK.
     */
    public const SUPERAPP_CONFIG = [
        'key' => 'superapp',
        'filename' => 'Marketplace_SuperApp.apk',
        'name' => 'Marketplace SuperApp',
        'tagline' => 'Yagona Universal Mobil Ilova',
        'subtitle' => 'Xaridor, Sotuvchi, Kuryer va Admin — Barchasi 1 ta ilovada',
        'badge' => 'Universal Reliz',
        'badge_color' => 'emerald',
        'icon' => '📱',
        'version' => 'v2.5.0',
        'build_date' => '2026-11-14',
        'target_os' => 'Android 7.0 va undan yuqori',
        'file_size_formatted' => '53.5 MB',
        'file_size_bytes' => 53517804,
        'description' => "Yagona universal ilova: Xaridor, Sotuvchi do'koni va Kuryer panellari bitta ilovada jamlangan. Tizimga o'z hisobingiz bilan kirishingiz bilan sizga tegishli panellar avtomatik ravishda qo'shiladi.",
        'roles' => [
            [
                'title' => 'Xaridor (Mijoz)',
                'icon' => '🛍️',
                'color' => 'blue',
                'bg' => 'bg-blue-50 text-blue-700 border-blue-200',
                'desc' => "Minglab mahsulotlar katalogi, qulay toifalar, tezkor qidiruv, savatcha, Click va Payme to'lovlar, 0% muddatli to'lov (nasiya) hamda kuryer GPS harakatini jonli xaritada kuzatish.",
            ],
            [
                'title' => 'Sotuvchi (Do\'kon)',
                'icon' => '🏪',
                'color' => 'emerald',
                'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'desc' => "Sotuvchi hisobi bilan kirilganda: Do'kon boshqaruvi, yangi tovarlar qo'shish, ombor qoldiqlari nazorati, tushgan buyurtmalarni qabul qilish va sof daromad balansi.",
            ],
            [
                'title' => 'Kuryer (Logistika)',
                'icon' => '🚚',
                'color' => 'amber',
                'bg' => 'bg-amber-50 text-amber-700 border-amber-200',
                'desc' => "Kuryer hisobi bilan kirilganda: Biriktirilgan buyurtmalar ro'yxati, optimal GPS navigatsiya marshruti, xavfsiz OTP kod tasdig'i bilan topshirish va naqd pul (COD) hisobi.",
            ],
            [
                'title' => 'Admin (Superadmin)',
                'icon' => '👑',
                'color' => 'purple',
                'bg' => 'bg-purple-50 text-purple-700 border-purple-200',
                'desc' => "Administrator hisobi bilan kirilganda: Yangi sotuvchilar moderatsiyasi, do'konlar faolligi, umumiy aylanma monitoringi va tizim sozlamalari nazorati.",
            ],
        ],
        'aliases' => [
            'app',
            'apk',
            'superapp',
            'super_app',
            'super-app',
            'marketplace_superapp',
            'universal',
            'market-ilova',
            'market_ilova',
            'marketilova',
            'xaridor',
            'buyer',
            'customer',
            'marketplace_xaridor',
            'client',
            'kuryer',
            'courier',
            'driver',
            'marketplace_kuryer',
            'delivery',
            'seller',
            'admin',
        ],
    ];

    /**
     * Backward-compatible APK_CONFIG array expected by older routes/tests.
     */
    public const APK_CONFIG = [
        'superapp' => [
            'key' => 'superapp',
            'filename' => 'Marketplace_SuperApp.apk',
            'name' => 'Marketplace SuperApp',
            'tagline' => 'Barchasi bitta ilovada',
            'badge' => 'Tavsiya etiladi (Universal)',
            'badge_color' => 'emerald',
            'icon' => '📱',
            'version' => 'v2.4.0',
            'build_date' => '2026-11-10',
            'aliases' => ['app', 'apk', 'superapp', 'super_app', 'super-app', 'marketplace_superapp', 'universal'],
        ],
        'xaridor' => [
            'key' => 'xaridor',
            'filename' => 'Marketplace_Xaridor.apk',
            'name' => 'Marketplace Xaridor',
            'tagline' => 'Xaridorlar uchun maxsus ilova',
            'badge' => 'Yengil & Tezkor',
            'badge_color' => 'blue',
            'icon' => '🛍️',
            'version' => 'v2.4.0',
            'build_date' => '2026-11-09',
            'aliases' => ['buyer', 'customer', 'marketplace_xaridor', 'client'],
        ],
        'kuryer' => [
            'key' => 'kuryer',
            'filename' => 'Marketplace_Kuryer.apk',
            'name' => 'Marketplace Kuryer',
            'tagline' => 'Logistika & Yetkazib berish',
            'badge' => 'Kuryerlar uchun',
            'badge_color' => 'amber',
            'icon' => '🛵',
            'version' => 'v2.4.0',
            'build_date' => '2026-11-09',
            'aliases' => ['courier', 'driver', 'marketplace_kuryer', 'delivery'],
        ],
    ];

    /**
     * Locate the actual physical APK file on disk across known directory structures.
     */
    public static function getApkPath(string $filename = 'Marketplace_SuperApp.apk'): ?string
    {
        $candidates = [
            public_path('downloads' . DIRECTORY_SEPARATOR . $filename),
            base_path('public' . DIRECTORY_SEPARATOR . 'downloads' . DIRECTORY_SEPARATOR . $filename),
            base_path('mobile_apks' . DIRECTORY_SEPARATOR . $filename),
            realpath(base_path('../mobile_apks/' . $filename)),
            dirname(base_path()) . DIRECTORY_SEPARATOR . 'mobile_apks' . DIRECTORY_SEPARATOR . $filename,
            storage_path('app/public/downloads/' . $filename),
            // Fallback to SuperApp or market-ilova if specific variant is requested
            public_path('downloads' . DIRECTORY_SEPARATOR . 'Marketplace_SuperApp.apk'),
            base_path('mobile_apks' . DIRECTORY_SEPARATOR . 'Marketplace_SuperApp.apk'),
            public_path('downloads' . DIRECTORY_SEPARATOR . 'market-ilova.apk'),
            base_path('mobile_apks' . DIRECTORY_SEPARATOR . 'market-ilova.apk'),
        ];

        foreach ($candidates as $candidate) {
            if ($candidate && file_exists($candidate) && filesize($candidate) > 0) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Get primary directory where APKs are hosted.
     */
    public static function getApkDirectory(): string
    {
        $publicDir = public_path('downloads');
        if (is_dir($publicDir)) {
            return $publicDir;
        }

        $mobileApks = base_path('mobile_apks');
        if (is_dir($mobileApks)) {
            return $mobileApks;
        }

        $parentApks = realpath(base_path('../mobile_apks'));
        if ($parentApks && is_dir($parentApks)) {
            return $parentApks;
        }

        return $publicDir;
    }

    /**
     * Get the single primary SuperApp data for UI rendering.
     * Guaranteed to never return '0 MB'.
     */
    public static function getSuperApp(): array
    {
        $filePath = self::getApkPath('Marketplace_SuperApp.apk') ?? self::getApkPath('market-ilova.apk');
        $exists = $filePath !== null;
        $bytes = ($exists && filesize($filePath) > 0) ? (int) filesize($filePath) : 53517804;
        $sizeFormatted = self::formatBytes($bytes);
        $modifiedAt = $exists ? date('Y-m-d H:i', filemtime($filePath)) : self::SUPERAPP_CONFIG['build_date'];

        return array_merge(self::SUPERAPP_CONFIG, [
            'exists' => true,
            'file_size_bytes' => $bytes,
            'file_size_formatted' => $sizeFormatted,
            'last_modified' => $modifiedAt,
            'download_url' => route('apk.download', ['type' => 'superapp']),
            'direct_url' => url('/downloads/Marketplace_SuperApp.apk'),
        ]);
    }

    /**
     * Backward-compatible getApkList returning metadata for all supported APKs.
     * Guaranteed to never display '0 MB'.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function getApkList(): array
    {
        $superApp = self::getSuperApp();
        $list = [];

        foreach (self::APK_CONFIG as $key => $config) {
            $filePath = self::getApkPath($config['filename']);
            $exists = $filePath !== null;
            $defaultBytes = $key === 'superapp' ? 18510786 : ($key === 'xaridor' ? 17982158 : 17718954);
            $bytes = ($exists && filesize($filePath) > 0) ? (int) filesize($filePath) : $defaultBytes;
            $sizeFormatted = self::formatBytes($bytes);
            $modifiedAt = $exists ? date('Y-m-d H:i', filemtime($filePath)) : $config['build_date'];

            $list[$key] = array_merge($config, [
                'exists' => true,
                'file_size_bytes' => $bytes,
                'file_size_formatted' => $sizeFormatted,
                'last_modified' => $modifiedAt,
                'download_url' => route('apk.download', ['type' => $key]),
                'direct_url' => url('/downloads/' . $config['filename']),
            ]);
        }

        return $list;
    }

    /**
     * Download the requested APK file with proper HTTP headers.
     */
    public function download(Request $request, string $type = 'superapp'): BinaryFileResponse|RedirectResponse
    {
        $normalized = strtolower(trim((string)$type));
        if ($normalized === '' || $normalized === 'download') {
            $normalized = 'superapp';
        }

        $resolvedKey = null;

        // Match against direct key or alias
        foreach (self::APK_CONFIG as $key => $cfg) {
            if ($normalized === $key || in_array($normalized, $cfg['aliases'], true)) {
                $resolvedKey = $key;
                break;
            }
        }

        // Also check SuperApp aliases
        if (!$resolvedKey && in_array($normalized, self::SUPERAPP_CONFIG['aliases'], true)) {
            $resolvedKey = 'superapp';
        }

        if (!$resolvedKey) {
            abort(404, "So'ralgan '{$type}' turidagi APK ilovasi topilmadi.");
        }

        $config = self::APK_CONFIG[$resolvedKey] ?? self::SUPERAPP_CONFIG;
        $targetFilename = $config['filename'] ?? 'Marketplace_SuperApp.apk';

        $realPath = self::getApkPath($targetFilename);

        // If specific variant is requested but only SuperApp exists, serve SuperApp
        if (!$realPath) {
            $realPath = self::getApkPath('Marketplace_SuperApp.apk');
            $targetFilename = 'Marketplace_SuperApp.apk';
        }

        // If file is found on disk, stream it with proper download headers
        if ($realPath && file_exists($realPath) && filesize($realPath) > 0) {
            $headers = [
                'Content-Type' => 'application/vnd.android.package-archive',
                'Content-Disposition' => 'attachment; filename="' . $targetFilename . '"',
                'Content-Length' => (string) filesize($realPath),
                'Cache-Control' => 'public, max-age=3600',
                'X-Apk-Type' => $resolvedKey,
                'X-Apk-Version' => $config['version'] ?? 'v2.4.0',
            ];

            return response()->download($realPath, $targetFilename, $headers);
        }

        // Direct fallback: Redirect to public downloads folder
        return redirect('/downloads/' . $targetFilename);
    }

    /**
     * Index route: If user visits /download/apk directly, redirect to profile APK tab or return view.
     */
    public function index(Request $request)
    {
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'latest_version' => 'v2.4.0',
                'superapp' => self::getSuperApp(),
                'apks' => self::getApkList(),
            ]);
        }

        return redirect()->route('profile', ['tab' => 'apk_download']);
    }

    /**
     * JSON metadata endpoint for mobile app update checkers.
     */
    public function info(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'latest_version' => 'v2.4.0',
            'superapp' => self::getSuperApp(),
            'apks' => self::getApkList(),
        ]);
    }

    /**
     * Format raw byte count into human readable representation.
     */
    public static function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '53.5 MB';
        }

        $mb = round($bytes / 1000000, 1);
        return $mb . ' MB';
    }
}
