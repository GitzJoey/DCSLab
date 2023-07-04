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
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $currentLocale = app()->getLocale();

        $incomingLocale = ($request->hasHeader('X-Localization')) ? $request->header('X-Localization') : 'en';

        if ($currentLocale != $incomingLocale) {
            app()->setLocale($incomingLocale);
        }

        return $next($request);
    }
}
