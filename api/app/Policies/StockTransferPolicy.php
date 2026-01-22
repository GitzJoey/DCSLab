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

        if ($user->hasPermission('stockTransfer-readAny')) {
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

        if ($user->hasPermission('stockTransfer-read')) {
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

        if ($user->hasPermission('stockTransfer-create')) {
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

        if ($user->hasPermission('stockTransfer-update')) {
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

        if ($user->hasPermission('stockTransfer-delete')) {
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
