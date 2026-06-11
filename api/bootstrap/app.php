<?php

use App\Http\Middleware\ValidateUser;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();

        $middleware->alias([
            'precognitive' => HandlePrecognitiveRequests::class,
            'validate.user' => ValidateUser::class,
        ]);

        $middleware->redirectTo(
            guests: '/login',
            users: fn () => '/dashboard'
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(function ($request, $e) {
            return $request->expectsJson() || $request->is('api/*');
        });
    })
    ->prefersJsonResponses()
    ->create();
