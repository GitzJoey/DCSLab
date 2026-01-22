<?php

namespace App\Policies;

use App\Enums\UserRoles;
use App\Models\SalePayment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SalePaymentPolicy
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

        if ($user->hasPermission('salePayment-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?SalePayment $salePayment = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRoles::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('salePayment-read')) {
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

        if ($user->hasPermission('salePayment-create')) {
            return true;
        }
    }

    public function update(User $user, ?SalePayment $salePayment = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRoles::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('salePayment-update')) {
            return true;
        }
    }

    public function delete(User $user, ?SalePayment $salePayment = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRoles::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('salePayment-delete')) {
            return true;
        }
    }

    public function restore(User $user, SalePayment $salePayment)
    {
        return false;
    }

    public function forceDelete(User $user, SalePayment $salePayment)
    {
        return false;
    }
}
