<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\PurchaseReceiptProductUnit;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PurchaseReceiptProductUnitPolicy
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

        if ($user->hasPermission('purchaseReceiptProductUnit-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?PurchaseReceiptProductUnit $purchaseReceiptProductUnit = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchaseReceiptProductUnit-read')) {
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

        if ($user->hasPermission('purchaseReceiptProductUnit-create')) {
            return true;
        }
    }

    public function update(User $user, ?PurchaseReceiptProductUnit $purchaseReceiptProductUnit = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchaseReceiptProductUnit-update')) {
            return true;
        }
    }

    public function delete(User $user, ?PurchaseReceiptProductUnit $purchaseReceiptProductUnit = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchaseReceiptProductUnit-delete')) {
            return true;
        }
    }

    public function restore(User $user, PurchaseReceiptProductUnit $purchaseReceiptProductUnit)
    {
        return false;
    }

    public function forceDelete(User $user, PurchaseReceiptProductUnit $purchaseReceiptProductUnit)
    {
        return false;
    }
}
