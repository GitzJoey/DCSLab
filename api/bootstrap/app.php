<?php

use App\Http\Middleware\RedirectIfAuthenticatedJson;
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
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->statefulApi();
        $middleware->alias([
            'guest' => RedirectIfAuthenticatedJson::class,
            'precognitive' => HandlePrecognitiveRequests::class,
            'validate.user' => ValidateUser::class,
        ]);

        $middleware->use([
            /*
            \App\Http\Middleware\ForceHeader::class,
            \App\Http\Middleware\LogRequestResponse::class,
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\XssSanitizer::class,
            */
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
