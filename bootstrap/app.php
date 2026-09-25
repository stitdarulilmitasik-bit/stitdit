<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'first.setup' => App\Http\Middleware\FirstSetup::class,
            'checkUser' => App\Http\Middleware\checkUser::class,
            'is-active' => App\Http\Middleware\isActive::class,
            'auth' => App\Http\Middleware\Authenticate::class,
            'guest' => App\Http\Middleware\RedirectIfAuthenticated::class,
            'dosen.guard' => App\Http\Middleware\UseDosenGuard::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) {
            if ($request->expectsJson()) return response()->json(['message'=>'Halaman tidak ditemukan'], 404);
            return response()->view('errors.404', [], 404);
        });
    })->create();
