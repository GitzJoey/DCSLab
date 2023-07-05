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
        $sanitizerStyle = $request->hasHeader('X-Sanitizer-Mode') ? $request->header('X-Sanitizer-Mode') : 'strip';

        $input = $request->all();

        $sanitizedInput = $this->sanitizeInput($sanitizerStyle, $input);

        $request->replace($sanitizedInput);

        return $next($request);
    }

    private function sanitizeInput(string $style, array &$input)
    {
        return collect($input)->map(function ($value, $key) use ($style) {
            if ($this->hasHtmlTags($value)) {
                return $this->sanitizeHtml($style, $value);
            }

            return $value;
        })->all();
    }

    private function hasHtmlTags($value)
    {
        return preg_match('/<[^>]+>/', $value) === 1;
    }

    private function sanitizeHtml($style, $value)
    {
        return $style == 'encode' ? htmlspecialchars($value, ENT_QUOTES | ENT_HTML5) : strip_tags($value);
    }
}
