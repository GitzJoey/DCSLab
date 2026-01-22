<?php

namespace App\Rules;

use App\Actions\CustomerAddress\CustomerAddressActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CustomerAddressStoreValidCode implements ValidationRule
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $customerAddressActions = new CustomerAddressActions();

            if (! $customerAddressActions->isUniqueCode($this->companyId, $value, null)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
