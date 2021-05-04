<?php

namespace App\Rules;

use App\Services\UserService;
use Illuminate\Container\Container;
use Illuminate\Contracts\Validation\Rule;

class sameEmail implements Rule
{
    private $id;
    private $userService;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($id)
    {
        $this->id = $id;

        $this->userService = Container::getInstance()->make(UserService::class);
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $usr = $this->userService->getUserById($this->id);

        return strcmp($usr['email'], $value) == 0;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('rules.same_email');
    }
}
