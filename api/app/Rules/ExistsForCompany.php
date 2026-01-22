<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class ExistsForCompany implements ValidationRule
{
    public function __construct(
        private string $table,
        private ?int $companyId
    ) {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_null($this->companyId) || is_null($value)) {
            return;
        }

        $exists = DB::table($this->table)
            ->where('id', $value)
            ->where('company_id', $this->companyId)
            ->exists();

        if (! $exists) {
            $fail('The selected :attribute is invalid.');
        }
    }
}
