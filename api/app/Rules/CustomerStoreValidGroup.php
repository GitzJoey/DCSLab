<?php

namespace App\Rules;

use App\Models\CustomerGroup;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CustomerStoreValidGroup implements ValidationRule
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value) {
            return;
        }

        $data = CustomerGroup::whereCompanyId($this->companyId)->where('id', $value);
        if ($data->doesntExist()) {
            $fail('rules.valid_customer_group')->translate();

            return;
        }
    }
}
