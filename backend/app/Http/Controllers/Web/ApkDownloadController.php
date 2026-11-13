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
     * Map of supported APK keys to disk filenames and metadata.
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
            'target' => 'Xaridor + Sotuvchi + Kuryer + Admin',
            'version' => 'v2.4.0',
            'build_date' => '2026-11-10',
            'description' => "Yagona universal ilova: Xaridor, Sotuvchi do'koni va Kuryer panellarini ilovadan chiqmasdan 1 ta bosishda almashtiring.",
            'features' => [
                "Barcha rollar (Xaridor, Sotuvchi, Kuryer) o'rtasida 1-klikda almashish",
                "Tezkor offline kesh va avtomatik ma'lumotlar sinxronizatsiyasi",
                "Kuryerlar uchun real-vaqt GPS kuzatuv va buyurtma yetkazish",
                "Sotuvchilar uchun mahsulotlar va ombor qoldiqlarini boshqarish",
                "Xaridorlar uchun tovarlar katalogi, 0% nasiya va Click/Payme to'lovlar",
            ],
            'aliases' => ['super_app', 'super-app', 'marketplace_superapp', 'universal'],
        ],
        'xaridor' => [
            'key' => 'xaridor',
            'filename' => 'Marketplace_Xaridor.apk',
            'name' => 'Marketplace Xaridor',
            'tagline' => 'Xaridorlar uchun maxsus ilova',
            'badge' => 'Yengil & Tezkor',
            'badge_color' => 'blue',
            'icon' => '🛍️',
            'target' => 'Faqat xaridlar va buyurtmalar',
            'version' => 'v2.4.0',
            'build_date' => '2026-11-09',
            'description' => "Eng qulay xarid ilovasi: tovarlar katalogi, tezkor qidiruv, savatcha, bo'lib to'lash va kuryerni xaritada kuzatish.",
            'features' => [
                "Minglab tovarlar katalogi, qulay toifalar va jonli qidiruv",
                "Click, Payme, Nasiya (Muddatli to'lov) va Naqd to'lov turlari",
                "Buyurtmalar tarixi, holat xronologiyasi va kuryer GPS kuzatuvi",
                "Sevimli mahsulotlar va chegiruvlar haqida xabarnomalar",
            ],
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
            'target' => 'Yetkazib berish xizmati va haydovchilar',
            'version' => 'v2.4.0',
            'build_date' => '2026-11-09',
            'description' => "Kuryerlar uchun optimallashtirilgan dastur: tayinланган buyurtmalar, GPS navigatsiya, xavfsiz OTP kod tasdig'i va naqd pul hisobi.",
            'features' => [
                "Biriktirilgan buyurtmalar ro'yxati va optimal yo'nalishlar",
                "Yetkazib berishda xavfsiz OTP tasdiqlash kodi",
                "Mijoz va do'kon manzillarini xaritada ko'rish va qo'ng'iroq qilish",
                "Naqd pul (COD) tushumlarini aniq qayd etish",
            ],
            'aliases' => ['courier', 'driver', 'marketplace_kuryer', 'delivery'],
        ],
    ];

    /**
     * Get the directory path where APK files are stored.
     */
    public static function getApkDirectory(): string
    {
        $primary = realpath(base_path('../mobile_apks'));
        if ($primary && is_dir($primary)) {
            return $primary;
        }

        $fallback = dirname(base_path()) . DIRECTORY_SEPARATOR . 'mobile_apks';
        if (is_dir($fallback)) {
            return $fallback;
        }

        return base_path('mobile_apks');
    }

    /**
     * Return enriched metadata list of available APKs.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function getApkList(): array
    {
        $dir = self::getApkDirectory();
        $list = [];

        foreach (self::APK_CONFIG as $key => $config) {
            $filePath = $dir . DIRECTORY_SEPARATOR . $config['filename'];
            $exists = file_exists($filePath);
            $bytes = $exists ? (int) filesize($filePath) : 0;
            $sizeFormatted = $exists ? self::formatBytes($bytes) : '0 MB';
            $modifiedAt = $exists ? date('Y-m-d H:i', filemtime($filePath)) : $config['build_date'];

            $list[$key] = array_merge($config, [
                'exists' => $exists,
                'file_size_bytes' => $bytes,
                'file_size_formatted' => $sizeFormatted,
                'last_modified' => $modifiedAt,
                'download_url' => route('apk.download', ['type' => $key]),
            ]);
        }

        return $list;
    }

    /**
     * Download the requested APK file with proper HTTP headers.
     */
    public function download(Request $request, string $type): BinaryFileResponse|RedirectResponse
    {
        $normalized = strtolower(trim($type));
        $resolvedKey = null;

        // Match against direct key or alias
        foreach (self::APK_CONFIG as $key => $cfg) {
            if ($normalized === $key || in_array($normalized, $cfg['aliases'], true)) {
                $resolvedKey = $key;
                break;
            }
        }

        if (!$resolvedKey) {
            abort(404, "So'ralgan '{$type}' turidagi APK ilovasi topilmadi.");
        }

        $config = self::APK_CONFIG[$resolvedKey];
        $dir = self::getApkDirectory();
        $filePath = $dir . DIRECTORY_SEPARATOR . $config['filename'];
        $realPath = realpath($filePath);

        // Security check: ensure path is within apk directory
        if (!$realPath || !file_exists($realPath) || !str_starts_with($realPath, realpath($dir) ?: $dir)) {
            abort(404, "Ilova fayli serverda mavjud emas yoki vaqtincha olib tashlangan.");
        }

        $headers = [
            'Content-Type' => 'application/vnd.android.package-archive',
            'Content-Disposition' => 'attachment; filename="' . $config['filename'] . '"',
            'Content-Length' => (string) filesize($realPath),
            'Cache-Control' => 'public, max-age=3600',
            'X-Apk-Type' => $resolvedKey,
            'X-Apk-Version' => $config['version'],
        ];

        return response()->download($realPath, $config['filename'], $headers);
    }

    /**
     * Index route: If user visits /download/apk directly, redirect to profile APK tab or return view.
     */
    public function index(Request $request)
    {
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Mobil ilovalar ro\'yxati',
                'apks' => self::getApkList(),
            ]);
        }

        // Redirect to profile page with APK tab active
        return redirect()->route('profile', ['tab' => 'apk_download']);
    }

    /**
     * JSON info endpoint for mobile app checks or external scripts.
     */
    public function info(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'latest_version' => 'v2.4.0',
            'apks' => self::getApkList(),
        ]);
    }

    /**
     * Format bytes to human readable format.
     */
    private static function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
