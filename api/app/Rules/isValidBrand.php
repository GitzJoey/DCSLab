<?php

namespace App\Rules;

use App\Models\Brand;
use Illuminate\Contracts\Validation\Rule;

class isValidBrand implements Rule
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
        $brandIds = Brand::where('company_id', '=', $this->company_id)->pluck('id');
        return $brandIds->contains($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('rules.valid_brand');
    }
}
