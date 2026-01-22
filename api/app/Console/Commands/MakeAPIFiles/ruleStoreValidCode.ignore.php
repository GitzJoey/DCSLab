<?php

namespace App\Rules;

use App\Actions\RepToPascalThis\RepToPascalThisActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class RepToPascalThisStoreValidCode implements ValidationRule
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $RepToCamelThisActions = new RepToPascalThisActions();

            if (! $RepToCamelThisActions->isUniqueCode($this->companyId, $value, null)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
