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
        $input = $request->all();

        $sanitizedInput = $this->sanitizeInput($input);

        $request->replace($sanitizedInput);

        return $next($request);
    }

    private function sanitizeInput(array &$input)
    {
        return collect($input)->map(function ($value, $key) {
            if ($this->hasHtmlTags($value)) {
                return $this->sanitizeHtml($value);
            }

            return $value;
        })->all();
    }

    private function hasHtmlTags($value)
    {
        return preg_match('/<[^>]+>/', $value) === 1;
    }

    private function sanitizeHtml($value)
    {
        return strip_tags($value);
        //return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5);
    }
}
