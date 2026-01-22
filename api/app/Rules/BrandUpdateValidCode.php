<?php

namespace App\Rules;

use App\Actions\Brand\BrandActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class BrandUpdateValidCode implements ValidationRule
{
    protected $companyId;

    protected $brand;

    public function __construct($companyId, $brand)
    {
        $this->companyId = $companyId;
        $this->brand = $brand;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $brandActions = new BrandActions();

            if (! $brandActions->isUniqueCode($this->companyId, $value, $this->brand->id)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
