<?php

namespace App\Rules;

use App\Models\Investor;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class InvestorUpdateValidName implements ValidationRule
{
    protected $companyId;

    protected $investor;

    public function __construct($companyId, $investor)
    {
        $this->companyId = $companyId;
        $this->investor = $investor;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $data = Investor::whereCompanyId($this->companyId)->where('name', $value);

        if ($data->exists() && $this->investor->name !== $value) {
            $fail('rules.unique_name')->translate();

            return;
        }
    }
}
