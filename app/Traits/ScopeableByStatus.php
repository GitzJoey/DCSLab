<?php

namespace App\Traits;

trait ScopeableByStatus {
    public function scopeWhereStatus($query, $status = null)
    {
        if ($status != null) {
            if (is_array($status)) {
                $query->whereIn('status', $status);
            } else {
                $query->where('status', '=', $status);
            }
        }
    }

    public function scopeWhereStatusActive($query)
    {
        $query->where('status', '=', 1);
    }
}
