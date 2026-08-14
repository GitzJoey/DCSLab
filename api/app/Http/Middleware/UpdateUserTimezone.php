<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UpdateUserTimezone
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (Auth::check()) {
            $timezone = $request->header('X-Timezone');

            if (is_string($timezone) && $timezone !== '') {
                $user = $request->user();

                if ($user && $user->timezone !== $timezone) {
                    $user->timezone = $timezone;
                    $user->save();
                }
            }
        }

        return $response;
    }
}
