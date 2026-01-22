<?php

namespace App\Policies;

use App\Enums\UserRoles;
use App\Models\SaleReceiptProductUnit;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SaleReceiptProductUnitPolicy
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

        if ($user->hasPermission('saleReceiptProductUnit-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?SaleReceiptProductUnit $saleReceiptProductUnit = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRoles::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('saleReceiptProductUnit-read')) {
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

        if ($user->hasPermission('saleReceiptProductUnit-create')) {
            return true;
        }
    }

    public function update(User $user, ?SaleReceiptProductUnit $saleReceiptProductUnit = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRoles::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('saleReceiptProductUnit-update')) {
            return true;
        }
    }

    public function delete(User $user, ?SaleReceiptProductUnit $saleReceiptProductUnit = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRoles::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('saleReceiptProductUnit-delete')) {
            return true;
        }
    }

    public function restore(User $user, SaleReceiptProductUnit $saleReceiptProductUnit)
    {
        return false;
    }

    public function forceDelete(User $user, SaleReceiptProductUnit $saleReceiptProductUnit)
    {
        return false;
    }
}
