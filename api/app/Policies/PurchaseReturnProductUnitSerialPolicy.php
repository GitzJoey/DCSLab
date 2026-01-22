<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\PurchaseReturnProductUnitSerial;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PurchaseReturnProductUnitSerialPolicy
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

        if ($user->hasPermission('purchaseReturnProductUnitSerial-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?PurchaseReturnProductUnitSerial $purchaseReturnProductUnitSerial = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchaseReturnProductUnitSerial-read')) {
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

        if ($user->hasPermission('purchaseReturnProductUnitSerial-create')) {
            return true;
        }
    }

    public function update(User $user, ?PurchaseReturnProductUnitSerial $purchaseReturnProductUnitSerial = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchaseReturnProductUnitSerial-update')) {
            return true;
        }
    }

    public function delete(User $user, ?PurchaseReturnProductUnitSerial $purchaseReturnProductUnitSerial = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchaseReturnProductUnitSerial-delete')) {
            return true;
        }
    }

    public function restore(User $user, PurchaseReturnProductUnitSerial $purchaseReturnProductUnitSerial)
    {
        return false;
    }

    public function forceDelete(User $user, PurchaseReturnProductUnitSerial $purchaseReturnProductUnitSerial)
    {
        return false;
    }
}
