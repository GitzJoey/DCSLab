<?php

namespace App\Rules;

use App\Actions\NonCapitalAdditionCategory\NonCapitalAdditionCategoryActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NonCapitalAdditionCategoryStoreValidCode implements ValidationRule
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $nonCapitalAdditionCategoryActions = new NonCapitalAdditionCategoryActions();

            if (! $nonCapitalAdditionCategoryActions->isUniqueCode($this->companyId, $value, null)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
