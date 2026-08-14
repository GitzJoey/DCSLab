<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\StockTransferItemSerial;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StockTransferItemSerialPolicy
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

        if ($user->hasPermission('stock_transfer_item_serial-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?StockTransferItemSerial $stockTransferItemSerial = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('stock_transfer_item_serial-read')) {
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

        if ($user->hasPermission('stock_transfer_item_serial-create')) {
            return true;
        }
    }

    public function update(User $user, ?StockTransferItemSerial $stockTransferItemSerial = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('stock_transfer_item_serial-update')) {
            return true;
        }
    }

    public function delete(User $user, ?StockTransferItemSerial $stockTransferItemSerial = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('stock_transfer_item_serial-delete')) {
            return true;
        }
    }

    public function restore(User $user, StockTransferItemSerial $stockTransferItemSerial)
    {
        return false;
    }

    public function forceDelete(User $user, StockTransferItemSerial $stockTransferItemSerial)
    {
        return false;
    }
}
