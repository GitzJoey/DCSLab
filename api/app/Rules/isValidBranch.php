<?php

namespace App\Rules;

use App\Models\Branch;
use Illuminate\Contracts\Validation\Rule;

class isValidBranch implements Rule
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
        $branchIds = Branch::whereIn('company_id', $companyIds)->pluck('id');
        return $branchIds->contains($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('rules.valid_branch');
    }
}
