<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Rules\mustResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use App\Models\User;
use App\Rules\inactiveUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->email.$request->ip());
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        Fortify::authenticateUsing(function (Request $request) {    
            $user = User::where('email', $request->email)->first();
            
            if ($user && Hash::check($request->password, $user->password)) {
                $request->validate([
                    'email' => [new inactiveUser($user)],
                    'password' => [new mustResetPassword($user)] 
                ]);
    
                return $user;
            }
            else {
                throw ValidationException::withMessages([
                    Fortify::email() => trans('auth.failed'),
                ]);
            }
        });

        Fortify::loginView(function() {
            return view('auth.login');
        });

        Fortify::registerView(function() {
            return view('auth.register');
        });

        Fortify::requestPasswordResetLinkView(function() {
            return view('auth.passwords.email');
        });

        Fortify::resetPasswordView(function($request) {
            return view('auth.passwords.reset', [
                'token' => $request->token,
                'email' => $request->get('email'),
            ]);
        });
    }
}
