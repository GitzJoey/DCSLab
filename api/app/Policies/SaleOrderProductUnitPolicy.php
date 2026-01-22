<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\SaleOrderProductUnit;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SaleOrderProductUnitPolicy
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

        if ($user->hasPermission('saleOrderProductUnit-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?SaleOrderProductUnit $saleOrderProductUnit = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('saleOrderProductUnit-read')) {
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

        if ($user->hasPermission('saleOrderProductUnit-create')) {
            return true;
        }
    }

    public function update(User $user, ?SaleOrderProductUnit $saleOrderProductUnit = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('saleOrderProductUnit-update')) {
            return true;
        }
    }

    public function delete(User $user, ?SaleOrderProductUnit $saleOrderProductUnit = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('saleOrderProductUnit-delete')) {
            return true;
        }
    }

    public function restore(User $user, SaleOrderProductUnit $saleOrderProductUnit)
    {
        return false;
    }

    public function forceDelete(User $user, SaleOrderProductUnit $saleOrderProductUnit)
    {
        return false;
    }
}
