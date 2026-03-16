<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\StockAdjustmentInProductSerial;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StockAdjustmentInProductSerialPolicy
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

        if ($user->hasPermission('stock_adjustment_in_product_serial-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?StockAdjustmentInProductSerial $stockAdjustmentInProductSerial = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('stock_adjustment_in_product_serial-read')) {
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

        if ($user->hasPermission('stock_adjustment_in_product_serial-create')) {
            return true;
        }
    }

    public function update(User $user, ?StockAdjustmentInProductSerial $stockAdjustmentInProductSerial = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('stock_adjustment_in_product_serial-update')) {
            return true;
        }
    }

    public function delete(User $user, ?StockAdjustmentInProductSerial $stockAdjustmentInProductSerial = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('stock_adjustment_in_product_serial-delete')) {
            return true;
        }
    }

    public function restore(User $user, StockAdjustmentInProductSerial $stockAdjustmentInProductSerial)
    {
        return false;
    }

    public function forceDelete(User $user, StockAdjustmentInProductSerial $stockAdjustmentInProductSerial)
    {
        return false;
    }
}
