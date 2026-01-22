<?php

namespace App\Rules;

use App\Actions\CustomerAddress\CustomerAddressActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CustomerAddressUpdateValidCode implements ValidationRule
{
    protected $companyId;

    protected $customerAddress;

    public function __construct($companyId, $customerAddress)
    {
        $this->companyId = $companyId;
        $this->customerAddress = $customerAddress;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $customerAddressActions = new CustomerAddressActions();

            if (! $customerAddressActions->isUniqueCode($this->companyId, $value, $this->customerAddress->id)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
