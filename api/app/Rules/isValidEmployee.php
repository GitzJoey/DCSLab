<?php

namespace App\Rules;

use App\Models\Employee;
use Illuminate\Contracts\Validation\Rule;

class isValidEmployee implements Rule
{
    private $companyId;
    
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($companyId)
    {
        $this->companyId = $companyId;
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
        if (! $this->companyId || ! $value) return false;

        $result = Employee::where([
            ['id', '=', $value],
            ['company_id', '=', $this->companyId],
        ])->exists();

        return $result;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('rules.valid_employee');
    }
}
