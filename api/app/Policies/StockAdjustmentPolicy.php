<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\StockAdjustment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StockAdjustmentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('stock_adjustment-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?StockAdjustment $stockAdjustment = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('stock_adjustment-read')) {
            return true;
        }
    }

    public function create(User $user)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('stock_adjustment-create')) {
            return true;
        }
    }

    public function update(User $user, ?StockAdjustment $stockAdjustment = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('stock_adjustment-update')) {
            return true;
        }
    }

    public function delete(User $user, ?StockAdjustment $stockAdjustment = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('stock_adjustment-delete')) {
            return true;
        }
    }

    public function restore(User $user, StockAdjustment $stockAdjustment)
    {
        return false;
    }

    public function forceDelete(User $user, StockAdjustment $stockAdjustment)
    {
        return false;
    }
}
