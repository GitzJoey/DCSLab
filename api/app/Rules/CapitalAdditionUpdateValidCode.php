<?php

namespace App\Rules;

use App\Actions\CapitalAddition\CapitalAdditionActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CapitalAdditionUpdateValidCode implements ValidationRule
{
    protected $companyId;

    protected $capitalAddition;

    public function __construct($companyId, $capitalAddition)
    {
        $this->companyId = $companyId;
        $this->capitalAddition = $capitalAddition;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $capitalAdditionActions = new CapitalAdditionActions();

            if (! $capitalAdditionActions->isUniqueCode($this->companyId, $value, $this->capitalAddition->id)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
