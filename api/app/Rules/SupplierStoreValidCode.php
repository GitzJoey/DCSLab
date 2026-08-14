<?php

namespace App\Rules;

use App\Actions\Supplier\SupplierActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SupplierStoreValidCode implements ValidationRule
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $supplierActions = new SupplierActions();

            if (! $supplierActions->isUniqueCode($this->companyId, $value, null)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
