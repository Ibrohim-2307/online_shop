<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiResponse
{
    /**
     * Return a standardized success response.
     */
    public static function success(mixed $data = null, string $message = 'Muvaffaqiyatli bajarildi', int $status = Response::HTTP_OK): JsonResponse
    {
        $requestId = request()->header('X-Request-Id') ?? request()->header('x-request-id');

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'request_id' => $requestId,
        ], $status);
    }

    /**
     * Return a standardized created (201) response.
     */
    public static function created(mixed $data = null, string $message = 'Muvaffaqiyatli yaratildi'): JsonResponse
    {
        return self::success($data, $message, Response::HTTP_CREATED);
    }

    /**
     * Return a standardized error response.
     */
    public static function error(string $message, string $code = 'BAD_REQUEST', mixed $details = null, int $status = Response::HTTP_BAD_REQUEST): JsonResponse
    {
        $requestId = request()->header('X-Request-Id') ?? request()->header('x-request-id');

        return response()->json([
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $message,
                'details' => $details,
            ],
            'request_id' => $requestId,
        ], $status);
    }

    /**
     * Return a standardized 401 Unauthorized response.
     */
    public static function unauthorized(string $message = "Autentifikatsiyadan o'tilmagan", string $code = 'UNAUTHORIZED', mixed $details = null): JsonResponse
    {
        return self::error($message, $code, $details, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Return a standardized 403 Forbidden response.
     */
    public static function forbidden(string $message = "Ushbu amalni bajarish uchun sizda yetarli ruxsat yo'q", string $code = 'FORBIDDEN', mixed $details = null): JsonResponse
    {
        return self::error($message, $code, $details, Response::HTTP_FORBIDDEN);
    }
}
