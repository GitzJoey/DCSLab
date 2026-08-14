<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\StockTransferItem;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StockTransferItemPolicy
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

        if ($user->hasPermission('stock_transfer_item-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?StockTransferItem $stockTransferItem = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('stock_transfer_item-read')) {
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

        if ($user->hasPermission('stock_transfer_item-create')) {
            return true;
        }
    }

    public function update(User $user, ?StockTransferItem $stockTransferItem = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('stock_transfer_item-update')) {
            return true;
        }
    }

    public function delete(User $user, ?StockTransferItem $stockTransferItem = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('stock_transfer_item-delete')) {
            return true;
        }
    }

    public function restore(User $user, StockTransferItem $stockTransferItem)
    {
        return false;
    }

    public function forceDelete(User $user, StockTransferItem $stockTransferItem)
    {
        return false;
    }
}
