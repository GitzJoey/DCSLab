<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\PurchaseReturn;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PurchaseReturnPolicy
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

        if ($user->hasPermission('purchase_return-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?PurchaseReturn $purchaseReturn = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchase_return-read')) {
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

        if ($user->hasPermission('purchase_return-create')) {
            return true;
        }
    }

    public function update(User $user, ?PurchaseReturn $purchaseReturn = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchase_return-update')) {
            return true;
        }
    }

    public function delete(User $user, ?PurchaseReturn $purchaseReturn = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchase_return-delete')) {
            return true;
        }
    }

    public function restore(User $user, PurchaseReturn $purchaseReturn)
    {
        return false;
    }

    public function forceDelete(User $user, PurchaseReturn $purchaseReturn)
    {
        return false;
    }
}
