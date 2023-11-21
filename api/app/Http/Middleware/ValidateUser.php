<?php

namespace App\Http\Middleware;

use App\Enums\RecordStatus;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class ValidateUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        if (is_null($user->password_changed_at)) {
            return response()->json([
                'message' => __('rules.must_reset_password')
            ]);
        }

        if (Carbon::now()->diffInDays(Carbon::parse($user->password_changed_at)->addDays(Config::get('dcslab.PASSWORD_EXPIRY_DAYS')), false) <= 0) {
            return response()->json([
                'message' => __('rules.must_reset_password')
            ]);
        }

        $profile = $user->profile;

        if (! $profile) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        if (! $profile->status == RecordStatus::ACTIVE) {
            return response()->json([
                'message' => __('rules.inactive_user')
            ], 401);
        }

        return $next($request);
    }
}
