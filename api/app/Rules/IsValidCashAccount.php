<?php

namespace App\Rules;

use App\Models\Branch;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IsValidCashAccount implements ValidationRule
{
    public ?int $branchId;

    public function __construct(?int $branchId)
    {
        $this->branchId = $branchId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->branchId && $value) {
            $branch = Branch::find($this->branchId);

            if (! $branch->cashAccounts?->pluck('id')->contains($value)) {
                $fail('rules.valid_cash_account')->translate();
            }
        }
    }
}
