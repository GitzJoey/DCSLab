<?php

namespace App\Rules;

use App\Actions\NonCapitalAddition\NonCapitalAdditionActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NonCapitalAdditionStoreValidCode implements ValidationRule
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $nonCapitalAdditionActions = new NonCapitalAdditionActions();

            if (! $nonCapitalAdditionActions->isUniqueCode($this->companyId, $value, null)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
