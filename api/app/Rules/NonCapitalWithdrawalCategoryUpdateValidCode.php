<?php

namespace App\Rules;

use App\Actions\NonCapitalWithdrawalCategory\NonCapitalWithdrawalCategoryActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NonCapitalWithdrawalCategoryUpdateValidCode implements ValidationRule
{
    protected $companyId;

    protected $nonCapitalWithdrawalCategory;

    public function __construct($companyId, $nonCapitalWithdrawalCategory)
    {
        $this->companyId = $companyId;
        $this->nonCapitalWithdrawalCategory = $nonCapitalWithdrawalCategory;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $nonCapitalWithdrawalCategoryActions = new NonCapitalWithdrawalCategoryActions();

            if (! $nonCapitalWithdrawalCategoryActions->isUniqueCode($this->companyId, $value, $this->nonCapitalWithdrawalCategory->id)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
