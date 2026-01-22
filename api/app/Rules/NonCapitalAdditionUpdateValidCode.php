<?php

namespace App\Rules;

use App\Actions\NonCapitalAddition\NonCapitalAdditionActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NonCapitalAdditionUpdateValidCode implements ValidationRule
{
    protected $companyId;

    protected $nonCapitalAddition;

    public function __construct($companyId, $nonCapitalAddition)
    {
        $this->companyId = $companyId;
        $this->nonCapitalAddition = $nonCapitalAddition;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $nonCapitalAdditionActions = new NonCapitalAdditionActions();

            if (! $nonCapitalAdditionActions->isUniqueCode($this->companyId, $value, $this->nonCapitalAddition->id)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
