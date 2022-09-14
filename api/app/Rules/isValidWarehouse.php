<?php

namespace App\Rules;

use App\Models\Warehouse;
use Illuminate\Contracts\Validation\Rule;

class isValidWarehouse implements Rule
{
    private $company_id;
    
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($company_id)
    {
        $this->company_id = $company_id;
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
        $result = Warehouse::where([
            ['id', '=', $value],
            ['company_id', '=', $this->company_id],
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
        return trans('rules.valid_warehouse');
    }
}
