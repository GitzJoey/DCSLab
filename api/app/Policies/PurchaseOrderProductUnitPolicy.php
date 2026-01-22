<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\PurchaseOrderProductUnit;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PurchaseOrderProductUnitPolicy
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

        if ($user->hasPermission('purchaseOrderProductUnit-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?PurchaseOrderProductUnit $purchaseOrderProductUnit = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchaseOrderProductUnit-read')) {
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

        if ($user->hasPermission('purchaseOrderProductUnit-create')) {
            return true;
        }
    }

    public function update(User $user, ?PurchaseOrderProductUnit $purchaseOrderProductUnit = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchaseOrderProductUnit-update')) {
            return true;
        }
    }

    public function delete(User $user, ?PurchaseOrderProductUnit $purchaseOrderProductUnit = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchaseOrderProductUnit-delete')) {
            return true;
        }
    }

    public function restore(User $user, PurchaseOrderProductUnit $purchaseOrderProductUnit)
    {
        return false;
    }

    public function forceDelete(User $user, PurchaseOrderProductUnit $purchaseOrderProductUnit)
    {
        return false;
    }
}
