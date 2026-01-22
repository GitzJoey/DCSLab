<?php

namespace App\Rules;

use App\Actions\Unit\UnitActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UnitStoreValidCode implements ValidationRule
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $unitActions = new UnitActions();

            if (! $unitActions->isUniqueCode($this->companyId, $value, null)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
