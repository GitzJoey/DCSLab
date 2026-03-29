<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\SaleProductUnitSerial;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SaleProductUnitSerialPolicy
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

        if ($user->hasPermission('sale_product_unit_serial-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?SaleProductUnitSerial $saleProductUnitSerial = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('sale_product_unit_serial-read')) {
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

        if ($user->hasPermission('sale_product_unit_serial-create')) {
            return true;
        }
    }

    public function update(User $user, ?SaleProductUnitSerial $saleProductUnitSerial = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('sale_product_unit_serial-update')) {
            return true;
        }
    }

    public function delete(User $user, ?SaleProductUnitSerial $saleProductUnitSerial = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('sale_product_unit_serial-delete')) {
            return true;
        }
    }

    public function restore(User $user, SaleProductUnitSerial $saleProductUnitSerial)
    {
        return false;
    }

    public function forceDelete(User $user, SaleProductUnitSerial $saleProductUnitSerial)
    {
        return false;
    }
}
