<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
    web: __DIR__.'/../routes/web.php',
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
    then: function () {
        Route::middleware('web')
            ->prefix('admin')
            ->name('admin.')
            ->group(base_path('routes/admin.php'));
    },
)
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens(except: [
            '/thanh-toan/momo-callback',
            '/thanh-toan/vnpay-callback',
        ]);

        // Đăng ký middleware alias
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);

        // Redirect người chưa login khi truy cập route cần auth
        $middleware->redirectGuestsTo(fn () => route('login'));

        // Redirect người đã login theo vai trò
        $middleware->redirectUsersTo(function () {
            if (auth()->check()) {
                return auth()->user()->getRedirectRoute();
            }
            return route('home');
        });

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();