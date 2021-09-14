<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class LocalizeApiRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        LaravelLocalization::setLocale($request->header('X-localization') ?: config('app.locale'));
        return $next($request);
    }
}
