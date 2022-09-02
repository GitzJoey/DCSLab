<?php

namespace App\Rules;

use App\Models\ProductGroup;
use Illuminate\Contracts\Validation\Rule;

class isValidProductGroup implements Rule
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
        $companyIds = auth()->user()->companies->pluck('id')->toArray();
        $productGroupIds = ProductGroup::whereIn('company_id', $companyIds)->pluck('id');
        return $productGroupIds->contains($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('rules.valid_product_group');
    }
}
