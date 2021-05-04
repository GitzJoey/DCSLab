<?php

namespace App\Actions\Fortify;

use App\Models\User;

use Illuminate\Container\Container;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

use App\Services\UserService;
use App\Services\Impls\UserServiceImpl;

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

        $instances = Container::getInstance();

        $usr = $instances->make(UserService::class)->register(
            $input['name'],
            $input['email'],
            $input['password'],
            $input['terms'],
        );

        return $usr;
    }
}
