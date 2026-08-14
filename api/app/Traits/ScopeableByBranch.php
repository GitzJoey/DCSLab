<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

trait ScopeableByBranch
{
    public function scopeWhereBranchId(
        Builder $query,
        Collection|array|int|string|null $table = null,
        Collection|array|int|null $branchId = null,
    ) {
        if (is_int($table) || is_array($table) || $table instanceof Collection) {
            $branchId = $table;
            $table = null;
        }

        if ($branchId != null) {
            $targetColumn = $table ? $table.'.branch_id' : 'branch_id';

            if (is_a($branchId, 'Illuminate\Support\Collection')) {
                $query->whereIn($targetColumn, $branchId->toArray());
            } elseif (is_array($branchId)) {
                $query->whereIn($targetColumn, $branchId);
            } else {
                $query->where($targetColumn, '=', $branchId);
            }
        }
    }
}
