<?php

namespace App\Rules;

use App\Actions\Brand\BrandActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class BrandStoreValidCode implements ValidationRule
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $brandActions = new BrandActions();

            if (! $brandActions->isUniqueCode($this->companyId, $value, null)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
