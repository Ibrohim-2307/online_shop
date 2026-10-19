<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class HealthController extends Controller
{
    /**
     * Check system health status.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $dbStatus = 'disconnected';
        $dbLatencyMs = null;

        try {
            $start = microtime(true);
            DB::select('SELECT 1 AS connected');
            $dbLatencyMs = round((microtime(true) - $start) * 1000, 2);
            $dbStatus = 'connected';
        } catch (Throwable $e) {
            $dbStatus = 'error: ' . $e->getMessage();
        }

        $healthy = ($dbStatus === 'connected');

        $data = [
            'status' => $healthy ? 'healthy' : 'unhealthy',
            'service' => 'Marketplace API',
            'api_version' => 'v1',
            'framework' => [
                'name' => 'Laravel',
                'version' => app()->version(),
            ],
            'runtime' => [
                'php_version' => PHP_VERSION,
                'os' => PHP_OS,
            ],
            'database' => [
                'driver' => config('database.default'),
                'status' => $dbStatus,
                'latency_ms' => $dbLatencyMs,
            ],
            'environment' => app()->environment(),
            'timestamp' => now()->toISOString(),
        ];

        return ApiResponse::success(
            data: $data,
            message: $healthy ? 'Tizim barqaror ishlamoqda' : 'Tizimda muammo aniqlandi',
            status: $healthy ? 200 : 503
        );
    }
}
