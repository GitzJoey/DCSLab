<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\StockAdjustmentCategory;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StockAdjustmentCategoryPolicy
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

        if ($user->hasPermission('stockAdjustmentCategory-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?StockAdjustmentCategory $stockAdjustmentCategory = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('stockAdjustmentCategory-read')) {
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

        if ($user->hasPermission('stockAdjustmentCategory-create')) {
            return true;
        }
    }

    public function update(User $user, ?StockAdjustmentCategory $stockAdjustmentCategory = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('stockAdjustmentCategory-update')) {
            return true;
        }
    }

    public function delete(User $user, ?StockAdjustmentCategory $stockAdjustmentCategory = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('stockAdjustmentCategory-delete')) {
            return true;
        }
    }

    public function restore(User $user, StockAdjustmentCategory $stockAdjustmentCategory)
    {
        return false;
    }

    public function forceDelete(User $user, StockAdjustmentCategory $stockAdjustmentCategory)
    {
        return false;
    }
}
