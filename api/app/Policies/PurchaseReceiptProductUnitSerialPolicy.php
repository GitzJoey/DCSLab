<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\PurchaseReceiptProductUnitSerial;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PurchaseReceiptProductUnitSerialPolicy
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

        if ($user->hasPermission('purchaseReceiptProductUnitSerial-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?PurchaseReceiptProductUnitSerial $purchaseReceiptProductUnitSerial = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchaseReceiptProductUnitSerial-read')) {
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

        if ($user->hasPermission('purchaseReceiptProductUnitSerial-create')) {
            return true;
        }
    }

    public function update(User $user, ?PurchaseReceiptProductUnitSerial $purchaseReceiptProductUnitSerial = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchaseReceiptProductUnitSerial-update')) {
            return true;
        }
    }

    public function delete(User $user, ?PurchaseReceiptProductUnitSerial $purchaseReceiptProductUnitSerial = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchaseReceiptProductUnitSerial-delete')) {
            return true;
        }
    }

    public function restore(User $user, PurchaseReceiptProductUnitSerial $purchaseReceiptProductUnitSerial)
    {
        return false;
    }

    public function forceDelete(User $user, PurchaseReceiptProductUnitSerial $purchaseReceiptProductUnitSerial)
    {
        return false;
    }
}
