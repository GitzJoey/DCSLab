<?php

namespace App\Actions\Fortify;

use App\Enums\RecordStatus;
use App\Enums\UserRoles;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
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
        ])->validate();

        $name = $input['name'];

        if ($name == trim($name) && strpos($name, ' ') !== false) {
            $pieces = explode(' ', $name);
            $first_name = $pieces[0];
            $last_name = $pieces[1];

            $name = str_replace(' ', '', $name);
        } else {
            $first_name = $name;
            $last_name = '';
        }

        $profile = [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'status' => RecordStatus::ACTIVE,
        ];

        $roles = [Role::where('name', UserRoles::USER->value)->first()->id];

        $usr = $this->create(
            $input,
            $roles,
            $profile
        );

        return $usr;
    }
}
