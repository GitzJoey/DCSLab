<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\StockAdjustmentOutItem;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StockAdjustmentOutItemPolicy
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

        if ($user->hasPermission('stock_adjustment_out_item-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?StockAdjustmentOutItem $stockAdjustmentOutItem = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('stock_adjustment_out_item-read')) {
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

        if ($user->hasPermission('stock_adjustment_out_item-create')) {
            return true;
        }
    }

    public function update(User $user, ?StockAdjustmentOutItem $stockAdjustmentOutItem = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('stock_adjustment_out_item-update')) {
            return true;
        }
    }

    public function delete(User $user, ?StockAdjustmentOutItem $stockAdjustmentOutItem = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('stock_adjustment_out_item-delete')) {
            return true;
        }
    }

    public function restore(User $user, StockAdjustmentOutItem $stockAdjustmentOutItem)
    {
        return false;
    }

    public function forceDelete(User $user, StockAdjustmentOutItem $stockAdjustmentOutItem)
    {
        return false;
    }
}
