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
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthorized',
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (is_null($user->password_changed_at)) {
            return response()->json([
                'message' => __('middleware.validate_user.must_reset_password'),
            ], Response::HTTP_FORBIDDEN);
        }

        if (Carbon::now()->diffInDays(Carbon::parse($user->password_changed_at)->addDays(Config::get('dcslab.PASSWORD_EXPIRY_DAYS')), false) <= 0) {
            return response()->json([
                'message' => __('middleware.validate_user.must_reset_password'),
            ], Response::HTTP_FORBIDDEN);
        }

        $profile = $user->profile;

        if (! $profile) {
            return response()->json([
                'message' => 'Unauthorized',
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (! $profile->status->value == RecordStatus::ACTIVE->value) {
            return response()->json([
                'message' => __('middleware.validate_user.inactive_user'),
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
