<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class XssSanitizer
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $sanitizerStyle = 'strip';

        if ($request->hasHeader('X-Sanitizer-Mode') && $request->header('X-Sanitizer-Mode') == 'encode') {
            $sanitizerStyle = 'encode';
        }

        $input = $request->all();

        array_walk_recursive($input, function (&$input) use ($sanitizerStyle) {
            if ($this->isContainScriptTag(($input))) {
                $input = $sanitizerStyle == 'encode' ? htmlspecialchars($input, ENT_QUOTES | ENT_HTML5) : strip_tags($input);
            }
        });

        $request->merge($input);

        return $next($request);
    }

    private function isContainScriptTag($input): bool
    {
        return preg_match("/<script[\s\S]*?>/", $input);
    }
}
