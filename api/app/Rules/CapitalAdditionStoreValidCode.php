<?php

namespace App\Rules;

use App\Actions\CapitalAddition\CapitalAdditionActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CapitalAdditionStoreValidCode implements ValidationRule
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $capitalAdditionActions = new CapitalAdditionActions();

            if (! $capitalAdditionActions->isUniqueCode($this->companyId, $value, null)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
