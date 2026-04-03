<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\StockAdjustmentInItem;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StockAdjustmentInItemPolicy
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

        if ($user->hasPermission('stock_adjustment_in_item-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?StockAdjustmentInItem $stockAdjustmentInItem = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('stock_adjustment_in_item-read')) {
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

        if ($user->hasPermission('stock_adjustment_in_item-create')) {
            return true;
        }
    }

    public function update(User $user, ?StockAdjustmentInItem $stockAdjustmentInItem = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('stock_adjustment_in_item-update')) {
            return true;
        }
    }

    public function delete(User $user, ?StockAdjustmentInItem $stockAdjustmentInItem = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('stock_adjustment_in_item-delete')) {
            return true;
        }
    }

    public function restore(User $user, StockAdjustmentInItem $stockAdjustmentInItem)
    {
        return false;
    }

    public function forceDelete(User $user, StockAdjustmentInItem $stockAdjustmentInItem)
    {
        return false;
    }
}
