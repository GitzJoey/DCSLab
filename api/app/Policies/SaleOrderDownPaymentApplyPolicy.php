<?php

namespace App\Policies;

use App\Enums\UserRoles;
use App\Models\SaleOrderDownPaymentApply;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SaleOrderDownPaymentApplyPolicy
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

        if ($user->hasPermission('saleOrderDownPaymentApply-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?SaleOrderDownPaymentApply $saleOrderDownPaymentApply = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRoles::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('saleOrderDownPaymentApply-read')) {
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

        if ($user->hasPermission('saleOrderDownPaymentApply-create')) {
            return true;
        }
    }

    public function update(User $user, ?SaleOrderDownPaymentApply $saleOrderDownPaymentApply = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRoles::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('saleOrderDownPaymentApply-update')) {
            return true;
        }
    }

    public function delete(User $user, ?SaleOrderDownPaymentApply $saleOrderDownPaymentApply = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRoles::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('saleOrderDownPaymentApply-delete')) {
            return true;
        }
    }

    public function restore(User $user, SaleOrderDownPaymentApply $saleOrderDownPaymentApply)
    {
        return false;
    }

    public function forceDelete(User $user, SaleOrderDownPaymentApply $saleOrderDownPaymentApply)
    {
        return false;
    }
}
