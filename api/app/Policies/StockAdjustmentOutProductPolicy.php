<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\StockAdjustmentOutProduct;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StockAdjustmentOutProductPolicy
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

        if ($user->hasPermission('stock_adjustment_out_product-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?StockAdjustmentOutProduct $stockAdjustmentOutProduct = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('stock_adjustment_out_product-read')) {
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

        if ($user->hasPermission('stock_adjustment_out_product-create')) {
            return true;
        }
    }

    public function update(User $user, ?StockAdjustmentOutProduct $stockAdjustmentOutProduct = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('stock_adjustment_out_product-update')) {
            return true;
        }
    }

    public function delete(User $user, ?StockAdjustmentOutProduct $stockAdjustmentOutProduct = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('stock_adjustment_out_product-delete')) {
            return true;
        }
    }

    public function restore(User $user, StockAdjustmentOutProduct $stockAdjustmentOutProduct)
    {
        return false;
    }

    public function forceDelete(User $user, StockAdjustmentOutProduct $stockAdjustmentOutProduct)
    {
        return false;
    }
}
