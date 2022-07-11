<?php

namespace App\Rules;

use App\Enums\RecordStatus;
use Illuminate\Contracts\Validation\Rule;

class deactivateDefaultCompany implements Rule
{
    private bool $isDefault;
    private int $status;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(bool $isDefault, int $status)
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
        if ($this->isDefault == true && $this->status == RecordStatus::INACTIVE->value) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('rules.company.deactivate_default_company');
    }
}
