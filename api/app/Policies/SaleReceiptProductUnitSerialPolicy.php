<?php

namespace App\Policies;

use App\Enums\UserRoles;
use App\Models\SaleReceiptProductUnitSerial;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SaleReceiptProductUnitSerialPolicy
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

        if ($user->hasPermission('saleReceiptProductUnitSerial-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?SaleReceiptProductUnitSerial $saleReceiptProductUnitSerial = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRoles::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('saleReceiptProductUnitSerial-read')) {
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

        if ($user->hasPermission('saleReceiptProductUnitSerial-create')) {
            return true;
        }
    }

    public function update(User $user, ?SaleReceiptProductUnitSerial $saleReceiptProductUnitSerial = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRoles::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('saleReceiptProductUnitSerial-update')) {
            return true;
        }
    }

    public function delete(User $user, ?SaleReceiptProductUnitSerial $saleReceiptProductUnitSerial = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRoles::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('saleReceiptProductUnitSerial-delete')) {
            return true;
        }
    }

    public function restore(User $user, SaleReceiptProductUnitSerial $saleReceiptProductUnitSerial)
    {
        return false;
    }

    public function forceDelete(User $user, SaleReceiptProductUnitSerial $saleReceiptProductUnitSerial)
    {
        return false;
    }
}
