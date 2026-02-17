<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\StockAdjustmentInProduct;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StockAdjustmentInProductPolicy
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

        if ($user->hasPermission('stock_adjustment_in_product-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?StockAdjustmentInProduct $stockAdjustmentInProduct = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('stock_adjustment_in_product-read')) {
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

        if ($user->hasPermission('stock_adjustment_in_product-create')) {
            return true;
        }
    }

    public function update(User $user, ?StockAdjustmentInProduct $stockAdjustmentInProduct = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('stock_adjustment_in_product-update')) {
            return true;
        }
    }

    public function delete(User $user, ?StockAdjustmentInProduct $stockAdjustmentInProduct = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('stock_adjustment_in_product-delete')) {
            return true;
        }
    }

    public function restore(User $user, StockAdjustmentInProduct $stockAdjustmentInProduct)
    {
        return false;
    }

    public function forceDelete(User $user, StockAdjustmentInProduct $stockAdjustmentInProduct)
    {
        return false;
    }
}
