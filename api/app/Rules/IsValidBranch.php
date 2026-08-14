<?php

namespace App\Rules;

use App\Models\Company;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IsValidBranch implements ValidationRule
{
    protected ?int $companyId;

    protected bool $required;

    public function __construct(?int $companyId, bool $required)
    {
        $this->companyId = $companyId;
        $this->required = $required;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value || $this->required) {
            $company = is_null($this->companyId) ? null : Company::find($this->companyId);

            if (! $company || ! $company->branches->pluck('id')->contains($value)) {
                $fail('rules.valid_branch')->translate();
            }
        }
    }
}
