<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\PurchaseProductUnitSerial;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PurchaseProductUnitSerialPolicy
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

        if ($user->hasPermission('purchaseProductUnitSerial-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?PurchaseProductUnitSerial $purchaseProductUnitSerial = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchaseProductUnitSerial-read')) {
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

        if ($user->hasPermission('purchaseProductUnitSerial-create')) {
            return true;
        }
    }

    public function update(User $user, ?PurchaseProductUnitSerial $purchaseProductUnitSerial = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchaseProductUnitSerial-update')) {
            return true;
        }
    }

    public function delete(User $user, ?PurchaseProductUnitSerial $purchaseProductUnitSerial = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchaseProductUnitSerial-delete')) {
            return true;
        }
    }

    public function restore(User $user, PurchaseProductUnitSerial $purchaseProductUnitSerial)
    {
        return false;
    }

    public function forceDelete(User $user, PurchaseProductUnitSerial $purchaseProductUnitSerial)
    {
        return false;
    }
}
