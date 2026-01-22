<?php

namespace App\Rules;

use App\Actions\Customer\CustomerActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CustomerStoreValidCode implements ValidationRule
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $customerActions = new CustomerActions();

            if (! $customerActions->isUniqueCode($this->companyId, $value, null)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
