<?php

namespace App\Rules;

use App\Models\Warehouse;
use Illuminate\Contracts\Validation\Rule;

class isValidWarehouse implements Rule
{
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
        $warehouseIds = Warehouse::where('company_id', '=', $this->company_id)->pluck('id');
        return $warehouseIds->contains($value);
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
