<?php

namespace App\Policies;

use App\Enums\UserRoles;
use App\Models\SaleProductUnit;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SaleProductUnitPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRoles::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('saleProductUnit-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?SaleProductUnit $saleProductUnit = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRoles::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('saleProductUnit-read')) {
            return true;
        }
    }

    public function create(User $user)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRoles::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('saleProductUnit-create')) {
            return true;
        }
    }

    public function update(User $user, ?SaleProductUnit $saleProductUnit = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRoles::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('saleProductUnit-update')) {
            return true;
        }
    }

    public function delete(User $user, ?SaleProductUnit $saleProductUnit = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRoles::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('saleProductUnit-delete')) {
            return true;
        }
    }

    public function restore(User $user, SaleProductUnit $saleProductUnit)
    {
        return false;
    }

    public function forceDelete(User $user, SaleProductUnit $saleProductUnit)
    {
        return false;
    }
}
