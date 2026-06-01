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
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->hasHeader('X-Sanitizer-Mode')) {
            $request->headers->set('X-Sanitizer-Mode', 'strip');
        }

        $sanitizerStyle = $request->header('X-Sanitizer-Mode') === 'encode' ? 'encode' : 'strip';

        $input = $request->all();

        array_walk_recursive($input, function (&$value) use ($sanitizerStyle) {
            if (is_string($value) && ! empty($value)) {
                if ($this->isContainScriptTag($value)) {
                    $value = $sanitizerStyle === 'encode' 
                        ? htmlspecialchars($value, ENT_QUOTES | ENT_HTML5) 
                        : strip_tags($value);
                }
            }
        });

        $request->merge($input);

        return $next($request);
    }

    private function isContainScriptTag(?string $input): bool
    {
        if (is_null($input)) {
            return false;
        }

        return (bool) preg_match("/<script[\s\S]*?>/i", $input);
    }
}