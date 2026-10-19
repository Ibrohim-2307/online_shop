<?php

use App\Http\Middleware\AssignRequestId;
use App\Support\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(AssignRequestId::class);
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureRole::class,
            'seller.tenant' => \App\Http\Middleware\EnsureSellerTenant::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return ApiResponse::error(
                    message: "Kiritilgan ma'lumotlarda xatolik bor",
                    code: 'VALIDATION_ERROR',
                    details: $e->errors(),
                    status: 422
                );
            }
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return ApiResponse::unauthorized(
                    message: "Tizimga kirilmagan yoki token yaroqsiz",
                    code: 'UNAUTHENTICATED'
                );
            }
        });

        $exceptions->render(function (AuthorizationException|AccessDeniedHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return ApiResponse::forbidden(
                    message: $e->getMessage() ?: "Ushbu amalni bajarish uchun sizda yetarli ruxsat yo'q",
                    code: 'FORBIDDEN'
                );
            }
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return ApiResponse::error(
                    message: "So'ralgan API resursi topilmadi",
                    code: 'RESOURCE_NOT_FOUND',
                    details: null,
                    status: 404
                );
            }
        });

        $exceptions->render(function (MethodNotAllowedHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return ApiResponse::error(
                    message: "Ushbu HTTP metodiga ruxsat berilmagan",
                    code: 'METHOD_NOT_ALLOWED',
                    details: null,
                    status: 405
                );
            }
        });
    })->create();
