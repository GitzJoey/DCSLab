<?php

namespace App\Rules;

use App\Actions\Unit\UnitActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UnitUpdateValidCode implements ValidationRule
{
    protected $companyId;

    protected $unit;

    public function __construct($companyId, $unit)
    {
        $this->companyId = $companyId;
        $this->unit = $unit;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $unitActions = new UnitActions();

            if (! $unitActions->isUniqueCode($this->companyId, $value, $this->unit->id)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
