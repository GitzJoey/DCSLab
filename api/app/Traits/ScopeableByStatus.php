<?php

namespace App\Traits;

use App\Enums\RecordStatusEnum;
use Illuminate\Database\Eloquent\Builder;

trait ScopeableByStatus
{
    public function scopeWhereStatus(Builder $query, array|RecordStatusEnum|null $status = null)
    {
        if ($status != null) {
            if (is_array($status)) {
                $query->whereIn('status', $status);
            } else {
                $query->where('status', '=', $status);
            }
        }
    }

    public function scopeWhereStatusActive(Builder $query)
    {
        $query->where('status', '=', RecordStatusEnum::ACTIVE);
    }

    public function scopeWhereStatusInactive(Builder $query)
    {
        $query->where('status', '=', RecordStatusEnum::INACTIVE);
    }
}
