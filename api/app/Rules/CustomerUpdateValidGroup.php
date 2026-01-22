<?php

namespace App\Rules;

use App\Models\CustomerGroup;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CustomerUpdateValidGroup implements ValidationRule
{
    protected $companyId;

    protected $customer;

    public function __construct($companyId, $customer)
    {
        $this->companyId = $companyId;
        $this->customer = $customer;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $data = CustomerGroup::whereCompanyId($this->companyId)->where('id', $value);

        if ($data->doesntExist() && $this->customer->group_id !== $value) {
            $fail('rules.valid_customer_group')->translate();

            return;
        }
    }
}
