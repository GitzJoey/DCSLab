<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\Role;
use App\Models\Setting;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        $usr = new User();
        $usr->name = $data['name'];
        $usr->email = $data['email'];
        $usr->password = Hash::make($data['password']);

        $usr->created_at = Carbon::now();
        $usr->updated_at = Carbon::now();

        $usr->save();

        $profile = new Profile();
        if ($data['name'] == trim($data['name']) && strpos($data['name'], ' ') !== false) {
            $pieces = explode(" ", $data['name']);
            $profile->first_name = $pieces[0];
            $profile->last_name = $pieces[1];
        } else {
            $profile->first_name = $data['name'];
        }

        $profile->created_at = Carbon::now();
        $profile->updated_at = Carbon::now();

        $usr->profile()->save($profile);

        $setting = new Setting();
        $setting->type = 'KEY_VALUE';
        $setting->key = 'THEMES.CODEBASE';
        $setting->value = 'corporate';

        $usr->settings()->save($setting);

        $user_role = Role::where('name', Config::get('const.DEFAULT.ROLE.DEV'))->first();

        $usr->attachRole($user_role);

        return $usr;
    }
}
