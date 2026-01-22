<?php

namespace App\Rules;

use App\Actions\RepToPascalThis\RepToPascalThisActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class RepToPascalThisUpdateValidCode implements ValidationRule
{
    protected $companyId;

    protected $RepToCamelThis;

    public function __construct($companyId, $RepToCamelThis)
    {
        $this->companyId = $companyId;
        $this->RepToCamelThis = $RepToCamelThis;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $RepToCamelThisActions = new RepToPascalThisActions();

            if (! $RepToCamelThisActions->isUniqueCode($this->companyId, $value, $this->RepToCamelThis->id)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
