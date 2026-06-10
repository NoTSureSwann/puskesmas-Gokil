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
        // Sanitasi global: strip HTML tags dari semua input string (anti-XSS)
        $middleware->prepend(\App\Http\Middleware\SanitizeInput::class);

        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);

        $middleware->alias([
            'role'           => \App\Http\Middleware\RoleMiddleware::class,
            'email.verified' => \App\Http\Middleware\CheckEmailVerified::class,
            'log.rme'        => \App\Http\Middleware\LogRmeAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
