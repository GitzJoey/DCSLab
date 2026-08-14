<?php

namespace App\Rules;

use App\Models\Company;
use App\Models\Warehouse;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class WarehouseUpdateValidName implements ValidationRule
{
    public function __construct(
        protected ?int $companyId,
        protected Warehouse $warehouse,
    ) {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $company = Company::find($this->companyId);
        if (! $company) {
            return;
        }

        if ($company->warehouses()
            ->where('warehouses.id', '<>', $this->warehouse->id)
            ->where('name', (string) $value)
            ->exists()) {
            $fail('rules.unique_name')->translate();
        }
    }
}
