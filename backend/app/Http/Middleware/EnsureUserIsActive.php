<?php

namespace App\Http\Middleware;

use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     * Ensures that any deactivated user cannot access protected endpoints,
     * even if holding a previously issued valid token.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            $freshUser = $user->fresh();

            if (!$freshUser || !$freshUser->is_active) {
                return ApiResponse::forbidden(
                    message: "Ushbu hisob faolsizlantirilgan. Tizimdan foydalanish cheklangan.",
                    code: 'ACCOUNT_DEACTIVATED'
                );
            }
        }

        return $next($request);
    }
}
