<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // إعدادات Middleware للأدوار (Spatie)
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        
        // 1. معالجة أخطاء التحقق (Validation Errors)
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation Error.',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        // 2. معالجة خطأ "المورد غير موجود" (Model Not Found)
        $exceptions->render(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resource not found.',
                ], 404);
            }
        });

        // 3. معالجة خطأ "غير مصادق عليه" (Authentication Exception)
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                ], 401);
            }
        });

        // 4. معالجة الأخطاء العامة (General Errors - 500)
        $exceptions->render(function (\Throwable $e, $request) {
            if ($request->expectsJson()) {
                // في وضع التصحيح (local) نعرض الخطأ الحقيقي، في الإنتاج نعرض رسالة عامة
                $message = config('app.debug') ? $e->getMessage() : 'Server Error.';
                
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 500);
            }
        });
    })->create();