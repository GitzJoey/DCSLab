<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class XssSanitizer
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $allowed_tags = ['<br>'];

        $input = $request->all();

        array_walk_recursive($input, function (&$input) use ($allowed_tags) {
            $input = strip_tags($input, $allowed_tags);
        });

        $request->merge($input);

        return $next($request);
    }
}
