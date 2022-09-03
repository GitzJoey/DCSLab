<?php

namespace App\Rules;

use App\Models\ProductUnit;
use Illuminate\Contracts\Validation\Rule;

class isValidProductUnit implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
        if ($value) {
            $companyIds = auth()->user()->companies->pluck('id')->toArray();
            $productUnitIds = ProductUnit::whereIn('company_id', $companyIds)->pluck('id');
            return $productUnitIds->contains($value);
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
        return trans('rules.valid_product_unit');
    }
}
