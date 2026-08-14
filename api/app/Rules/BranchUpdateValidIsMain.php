<?php

namespace App\Rules;

use App\Models\Branch;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class BranchUpdateValidIsMain implements ValidationRule
{
    private Branch $branch;

    public function __construct(Branch $branch)
    {
        $this->branch = $branch;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $company = $this->branch->company;
        if ($company->branches->count() == 1 && $value == false) {
            $fail('rules.branch.set_branch_to_non_main')->translate();
        }
    }
}
