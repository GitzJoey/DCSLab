<?php

namespace App\Rules;

use App\Actions\NonCapitalWithdrawalCategory\NonCapitalWithdrawalCategoryActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NonCapitalWithdrawalCategoryStoreValidCode implements ValidationRule
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $nonCapitalWithdrawalCategoryActions = new NonCapitalWithdrawalCategoryActions();

            if (! $nonCapitalWithdrawalCategoryActions->isUniqueCode($this->companyId, $value, null)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
