<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class EnsureRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(Response::HTTP_UNAUTHORIZED, "Autentifikatsiyadan o'tilmagan");
        }

        // Admin has universal bypass for administrative checks
        if ($user->hasRole('admin')) {
            return $next($request);
        }

        if (!$user->hasRole($roles)) {
            throw new AccessDeniedHttpException(
                "Ushbu amalni bajarish uchun ruxsat etilgan rollar: " . implode(', ', $roles)
            );
        }

        return $next($request);
    }
}
