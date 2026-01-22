<?php

namespace App\Rules;

use App\Actions\SalesOrder\SalesOrderActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SalesOrderUpdateValidCode implements ValidationRule
{
    protected $companyId;

    protected $salesOrder;

    public function __construct($companyId, $salesOrder)
    {
        $this->companyId = $companyId;
        $this->salesOrder = $salesOrder;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $salesOrderActions = new SalesOrderActions();

            if (! $salesOrderActions->isUniqueCode($this->companyId, $value, $this->salesOrder->id)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
