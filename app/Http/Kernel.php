<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     */
    protected $middleware = [
        // \App\Http\Middleware\TrustProxies::class,
        // \Fruitcake\Cors\HandleCors::class,
        // \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        // \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        // \App\Http\Middleware\TrimStrings::class,
        // \Illuminate\Http\Middleware\SetCacheHeaders::class,
    ];

    /**
     * The application's route middleware groups.
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\SecurityHeaders::class,
            \App\Http\Middleware\ContentSecurityPolicy::class,
            \App\Http\Middleware\SetLocale::class,
            // \App\Http\Middleware\EncryptCookies::class,
            // \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            // \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            // \App\Http\Middleware\VerifyCsrfToken::class,
            // \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
        'api' => [
            // \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
            // \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * The application's route middleware.
     */
    protected $routeMiddleware = [
        'feature' => \App\Http\Middleware\CheckFeatureEnabled::class,
        'feature.access' => \App\Http\Middleware\CheckFeatureAccess::class,
        'login.lockout' => \App\Http\Middleware\LoginLockout::class,
        'recaptcha' => \App\Http\Middleware\VerifyRecaptcha::class,
        'throttle.login' => \App\Http\Middleware\ThrottleLogins::class,
        'check.installed' => \App\Http\Middleware\CheckInstalled::class,
        'check.not.installed' => \App\Http\Middleware\CheckNotInstalled::class,
        // 'auth' => \App\Http\Middleware\Authenticate::class,
        // 'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
    ];
}
