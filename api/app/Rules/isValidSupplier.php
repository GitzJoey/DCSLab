<?php

namespace App\Rules;

use App\Models\Supplier;
use Illuminate\Contracts\Validation\Rule;

class isValidSupplier implements Rule
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
        $supplierIds = Supplier::where('company_id', '=', $this->company_id)->pluck('id');
        return $supplierIds->contains($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('rules.valid_supplier');
    }
}
