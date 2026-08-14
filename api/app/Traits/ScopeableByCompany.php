<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

trait ScopeableByCompany
{
    public function scopeWhereCompanyId(
        Builder $query,
        Collection|array|int|string|null $table = null,
        Collection|array|int|null $companyId = null,
    ) {
        if (is_int($table) || is_array($table) || $table instanceof Collection) {
            $companyId = $table;
            $table = null;
        }

        if ($companyId != null) {
            $targetColumn = $table ? $table.'.company_id' : 'company_id';

            if (is_a($companyId, 'Illuminate\Support\Collection')) {
                $query->whereIn($targetColumn, $companyId->toArray());
            } elseif (is_array($companyId)) {
                $query->whereIn($targetColumn, $companyId);
            } else {
                $query->where($targetColumn, '=', $companyId);
            }
        }
    }
}
