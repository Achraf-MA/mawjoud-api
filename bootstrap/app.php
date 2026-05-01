<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
        // Exclude all API routes from CSRF verification — Sanctum uses XSRF-TOKEN header instead
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);

        $middleware->api(prepend: [
            EnsureFrontendRequestsAreStateful::class,
        ]);
        $middleware->use([
        \Illuminate\Http\Middleware\HandleCors::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Validation errors
    $exceptions->render(function (ValidationException $e, $request) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'data' => null,
            'errors' => $e->errors(),
        ], 422);
    });

    // HTTP errors (403, 404, etc.)
    $exceptions->render(function (HttpException $e, $request) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage() ?: 'Error',
            'data' => null,
            'errors' => null,
        ], $e->getStatusCode());
    });
    })->create();