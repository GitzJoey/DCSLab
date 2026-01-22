<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\PurchaseReturnProductUnit;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PurchaseReturnProductUnitPolicy
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

        if ($user->hasPermission('purchaseReturnProductUnit-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?PurchaseReturnProductUnit $purchaseReturnProductUnit = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchaseReturnProductUnit-read')) {
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

        if ($user->hasPermission('purchaseReturnProductUnit-create')) {
            return true;
        }
    }

    public function update(User $user, ?PurchaseReturnProductUnit $purchaseReturnProductUnit = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchaseReturnProductUnit-update')) {
            return true;
        }
    }

    public function delete(User $user, ?PurchaseReturnProductUnit $purchaseReturnProductUnit = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchaseReturnProductUnit-delete')) {
            return true;
        }
    }

    public function restore(User $user, PurchaseReturnProductUnit $purchaseReturnProductUnit)
    {
        return false;
    }

    public function forceDelete(User $user, PurchaseReturnProductUnit $purchaseReturnProductUnit)
    {
        return false;
    }
}
