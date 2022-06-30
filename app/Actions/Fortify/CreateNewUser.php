<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Container\Container;
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
            'terms' => 'required',
            'captcha' => 'required|captcha',
        ])->validate();

        $instances = Container::getInstance();

        $usr = $instances->make(UserService::class)->register($input);

        return $usr;
    }
}
