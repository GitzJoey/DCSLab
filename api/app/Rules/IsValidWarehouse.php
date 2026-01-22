<?php

namespace App\Rules;

use App\Models\Company;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IsValidWarehouse implements ValidationRule
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
            $company = Company::find($this->companyId);
            if (! $company->warehouses->pluck('id')->contains($value)) {
                $fail('rules.valid_warehouse')->translate();
            }
        }
    }
}
