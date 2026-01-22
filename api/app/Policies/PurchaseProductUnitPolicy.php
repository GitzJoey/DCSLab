<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\PurchaseProductUnit;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PurchaseProductUnitPolicy
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

        if ($user->hasPermission('purchaseProductUnit-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?PurchaseProductUnit $purchaseProductUnit = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchaseProductUnit-read')) {
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

        if ($user->hasPermission('purchaseProductUnit-create')) {
            return true;
        }
    }

    public function update(User $user, ?PurchaseProductUnit $purchaseProductUnit = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchaseProductUnit-update')) {
            return true;
        }
    }

    public function delete(User $user, ?PurchaseProductUnit $purchaseProductUnit = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchaseProductUnit-delete')) {
            return true;
        }
    }

    public function restore(User $user, PurchaseProductUnit $purchaseProductUnit)
    {
        return false;
    }

    public function forceDelete(User $user, PurchaseProductUnit $purchaseProductUnit)
    {
        return false;
    }
}
