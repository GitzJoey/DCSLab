<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\StockTransferProductUnit;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StockTransferProductUnitPolicy
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

        if ($user->hasPermission('stockTransferProductUnit-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?StockTransferProductUnit $stockTransferProductUnit = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('stockTransferProductUnit-read')) {
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

        if ($user->hasPermission('stockTransferProductUnit-create')) {
            return true;
        }
    }

    public function update(User $user, ?StockTransferProductUnit $stockTransferProductUnit = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('stockTransferProductUnit-update')) {
            return true;
        }
    }

    public function delete(User $user, ?StockTransferProductUnit $stockTransferProductUnit = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('stockTransferProductUnit-delete')) {
            return true;
        }
    }

    public function restore(User $user, StockTransferProductUnit $stockTransferProductUnit)
    {
        return false;
    }

    public function forceDelete(User $user, StockTransferProductUnit $stockTransferProductUnit)
    {
        return false;
    }
}
