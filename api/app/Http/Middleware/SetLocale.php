<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $incomingLocale = $request->header('X-Localization', 'en');

        if (! in_array($incomingLocale, ['en', 'id'])) {
            $incomingLocale = 'en';
        }

        if (app()->getLocale() !== $incomingLocale) {
            app()->setLocale($incomingLocale);
        }

        return $next($request);
    }
}
