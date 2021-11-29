<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class deactivateDefaultCompany implements Rule
{
    private $isDefault;
    private $status;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($isDefault, $status)
    {
        $this->isDefault = $isDefault;
        $this->status = $status;
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
        if ($this->isDefault == true && $this->status == 0) return false;
        else return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('rules.deactivate_default_company');
    }
}
