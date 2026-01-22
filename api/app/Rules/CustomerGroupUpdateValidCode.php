<?php

namespace App\Rules;

use App\Actions\CustomerGroup\CustomerGroupActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CustomerGroupUpdateValidCode implements ValidationRule
{
    protected $companyId;

    protected $customerGroup;

    public function __construct($companyId, $customerGroup)
    {
        $this->companyId = $companyId;
        $this->customerGroup = $customerGroup;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $customerGroupActions = new CustomerGroupActions();

            if (! $customerGroupActions->isUniqueCode($this->companyId, $value, $this->customerGroup->id)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
