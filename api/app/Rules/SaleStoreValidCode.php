<?php

namespace App\Rules;

use App\Actions\Sale\SaleActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SaleStoreValidCode implements ValidationRule
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $saleActions = new SaleActions();

            if (! $saleActions->isUniqueCode($this->companyId, $value, null)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
