<?php

namespace App\Rules;

use App\Actions\NonCapitalAdditionCategory\NonCapitalAdditionCategoryActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NonCapitalAdditionCategoryUpdateValidCode implements ValidationRule
{
    protected $companyId;

    protected $nonCapitalAdditionCategory;

    public function __construct($companyId, $nonCapitalAdditionCategory)
    {
        $this->companyId = $companyId;
        $this->nonCapitalAdditionCategory = $nonCapitalAdditionCategory;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $nonCapitalAdditionCategoryActions = new NonCapitalAdditionCategoryActions();

            if (! $nonCapitalAdditionCategoryActions->isUniqueCode($this->companyId, $value, $this->nonCapitalAdditionCategory->id)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
