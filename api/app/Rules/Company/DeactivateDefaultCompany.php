<?php

namespace App\Rules\Company;

use App\Enums\RecordStatus;
use App\Models\Company;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class DeactivateDefaultCompany implements ValidationRule
{
    private Company $company;

    public function __construct(Company $company)
    {
        $this->company = $company;
    }

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->company->isDefault == true && $value == RecordStatus::INACTIVE->value) {
            $fail('rules.company.deactivate_default_company')->translate();
        }
    }
}
