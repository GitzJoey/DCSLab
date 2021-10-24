<?php

namespace App\Rules;

use App\Services\UserService;
use Illuminate\Container\Container;
use Illuminate\Contracts\Validation\Rule;

class maxTokens implements Rule
{
    private $email;
    private $maxCount;
    private $userService;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($email = '', $maxCount = 2)
    {
        $this->email = $email;
        $this->maxCount = $maxCount;
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
        $parameters =  array (
            'readByEmail' => $this->email
        );

        $usr = $this->userService->read($parameters);

        if (!$usr) return false;

        return !($usr->tokens()->count() > $this->maxCount);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('rules.too_many_tokens');
    }
}
