<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\StockTransfer;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StockTransferPolicy
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

        if ($user->hasPermission('stock_transfer-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?StockTransfer $stockTransfer = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('stock_transfer-read')) {
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

        if ($user->hasPermission('stock_transfer-create')) {
            return true;
        }
    }

    public function update(User $user, ?StockTransfer $stockTransfer = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('stock_transfer-update')) {
            return true;
        }
    }

    public function delete(User $user, ?StockTransfer $stockTransfer = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('stock_transfer-delete')) {
            return true;
        }
    }

    public function restore(User $user, StockTransfer $stockTransfer)
    {
        return false;
    }

    public function forceDelete(User $user, StockTransfer $stockTransfer)
    {
        return false;
    }
}
