<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

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
        $this->sanitize($input);
        $request->replace($input);

        return $next($request);
    }

    private function sanitize(array &$input)
    {
        foreach ($input as &$value) {
            if (is_array($value)) {
                $this->sanitize($value);
            } else {
                $value = $this->cleanInput($value);
            }
        }
    }

    private function cleanInput($value)
    {
        if (is_string($value)) {
            $value = Str::ascii($value);
            $value = strip_tags($value);
            $value = htmlentities($value, ENT_QUOTES, 'UTF-8', false);

            return new HtmlString($value);
        }

        return $value;
    }
}
