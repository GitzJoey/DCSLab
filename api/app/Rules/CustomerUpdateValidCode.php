<?php

namespace App\Rules;

use App\Actions\Customer\CustomerActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CustomerUpdateValidCode implements ValidationRule
{
    protected $companyId;

    protected $customer;

    public function __construct($companyId, $customer)
    {
        $this->companyId = $companyId;
        $this->customer = $customer;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $customerActions = new CustomerActions();

            if (! $customerActions->isUniqueCode($this->companyId, $value, $this->customer->id)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
