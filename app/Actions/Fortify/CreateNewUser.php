<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Models\Role;
use App\Models\Profile;
use App\Models\Setting;

use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array  $input
     * @return \App\Models\User
     */
    public function create(array $input)
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
            'terms' => 'required'
        ])->validate();

        $usr = new User();
        $usr->name = $input['name'];
        $usr->email = $input['email'];
        $usr->password = Hash::make($input['password']);

        $usr->created_at = Carbon::now();
        $usr->updated_at = Carbon::now();

        $usr->save();

        $profile = new Profile();
        if ($input['name'] == trim($input['name']) && strpos($input['name'], ' ') !== false) {
            $pieces = explode(" ", $input['name']);
            $profile->first_name = $pieces[0];
            $profile->last_name = $pieces[1];
        } else {
            $profile->first_name = $input['name'];
        }

        $profile->created_at = Carbon::now();
        $profile->updated_at = Carbon::now();

        $usr->profile()->save($profile);

        $setting = new Setting();
        $setting->type = 'KEY_VALUE';
        $setting->key = 'THEMES.CODEBASE';
        $setting->value = 'corporate';

        $usr->settings()->save($setting);

        $user_role = Role::where('name', Config::get('const.DEFAULT.ROLE.USER'))->first();

        $usr->attachRole($user_role);

        $usr->createToken(Config::get('const.DEFAULT.API_TOKEN_NAME'));

        return $usr;
    }
}
