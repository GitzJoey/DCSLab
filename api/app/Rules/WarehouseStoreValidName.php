<?php

namespace App\Rules;

use App\Models\Company;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class WarehouseStoreValidName implements ValidationRule
{
    public function __construct(
        protected ?int $companyId,
    ) {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $company = Company::find($this->companyId);
        if (! $company) {
            return;
        }

        if ($company->warehouses()->where('name', (string) $value)->exists()) {
            $fail('rules.unique_name')->translate();
        }
    }
}
