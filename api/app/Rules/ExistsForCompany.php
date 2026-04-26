<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ExistsForCompany implements ValidationRule
{
    private static array $hasDeletedAtColumnCache = [];

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
            ->where('company_id', $this->companyId);

        if ($this->tableHasDeletedAtColumn()) {
            $exists->whereNull('deleted_at');
        }

        if (! $exists->exists()) {
            $fail('The selected :attribute is invalid.');
        }
    }

    private function tableHasDeletedAtColumn(): bool
    {
        if (! array_key_exists($this->table, self::$hasDeletedAtColumnCache)) {
            self::$hasDeletedAtColumnCache[$this->table] = Schema::hasColumn($this->table, 'deleted_at');
        }

        return self::$hasDeletedAtColumnCache[$this->table];
    }
}
