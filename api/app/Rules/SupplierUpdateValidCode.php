<?php

namespace App\Rules;

use App\Actions\Supplier\SupplierActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SupplierUpdateValidCode implements ValidationRule
{
    protected $companyId;

    protected $supplier;

    public function __construct($companyId, $supplier)
    {
        $this->companyId = $companyId;
        $this->supplier = $supplier;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $supplierActions = new SupplierActions();

            if (! $supplierActions->isUniqueCode($this->companyId, $value, $this->supplier->id)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
