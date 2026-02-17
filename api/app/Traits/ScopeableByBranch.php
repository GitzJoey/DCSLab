<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

trait ScopeableByBranch
{
    public function scopeWhereBranchId(Builder $query, Collection|array|int|null $branchId = null)
    {
        if ($branchId != null) {
            if (is_a($branchId, 'Illuminate\Support\Collection')) {
                $query->whereIn('branch_id', $branchId->toArray());
            } elseif (is_array($branchId)) {
                $query->whereIn('branch_id', $branchId);
            } else {
                $query->where('branch_id', '=', $branchId);
            }
        }
    }
}
