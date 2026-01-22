<?php

namespace App\Rules;

use App\Actions\Sale\SaleActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SaleUpdateValidCode implements ValidationRule
{
    protected $companyId;

    protected $sale;

    public function __construct($companyId, $sale)
    {
        $this->companyId = $companyId;
        $this->sale = $sale;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $saleActions = new SaleActions();

            if (! $saleActions->isUniqueCode($this->companyId, $value, $this->sale->id)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
