<?php

namespace App\Rules;

use App\Services\UserService;

use Carbon\Carbon;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Config;
use Illuminate\Contracts\Validation\Rule;

class mustResetPassword implements Rule
{
    private $email;
    private $userService;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($email)
    {
        $this->email = $email;
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
        $userId = $this->userService->readBy('EMAIL', $this->email);

        if ($userId->password_changed_at == null) return false;
        if (Carbon::now() > Carbon::createFromFormat('Y-m-d H:i:s', $userId->password_changed_at)->addDay(Config::get('const.DEFAULT.PASSWORD_EXPIRY_DAYS'))) return false;

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('rules.must_reset_password');
    }
}
