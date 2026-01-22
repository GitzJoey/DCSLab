<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\SaleOrderDownPayment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SaleOrderDownPaymentPolicy
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

        if ($user->hasPermission('saleOrderDownPayment-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?SaleOrderDownPayment $saleOrderDownPayment = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('saleOrderDownPayment-read')) {
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

        if ($user->hasPermission('saleOrderDownPayment-create')) {
            return true;
        }
    }

    public function update(User $user, ?SaleOrderDownPayment $saleOrderDownPayment = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('saleOrderDownPayment-update')) {
            return true;
        }
    }

    public function delete(User $user, ?SaleOrderDownPayment $saleOrderDownPayment = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('saleOrderDownPayment-delete')) {
            return true;
        }
    }

    public function restore(User $user, SaleOrderDownPayment $saleOrderDownPayment)
    {
        return false;
    }

    public function forceDelete(User $user, SaleOrderDownPayment $saleOrderDownPayment)
    {
        return false;
    }
}
