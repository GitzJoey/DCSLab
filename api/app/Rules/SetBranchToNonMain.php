<?php

namespace App\Rules;

use App\Models\Branch;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SetBranchToNonMain implements ValidationRule
{
    private Branch $branch;

    public function __construct(Branch $branch)
    {
        $this->branch = $branch;
    }
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $company = $this->branch->company;
        if (! boolval($value) &&
            $company &&
            $company->branches->count() == 1) {
                $fail('rules.branch.set_branch_to_non_main')->translate();
            }
    }
}
