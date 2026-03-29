<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
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

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('sale_order_down_payment_apply-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?SaleOrderDownPaymentApply $saleOrderDownPaymentApply = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('sale_order_down_payment_apply-read')) {
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

        if ($user->hasPermission('sale_order_down_payment_apply-create')) {
            return true;
        }
    }

    public function update(User $user, ?SaleOrderDownPaymentApply $saleOrderDownPaymentApply = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('sale_order_down_payment_apply-update')) {
            return true;
        }
    }

    public function delete(User $user, ?SaleOrderDownPaymentApply $saleOrderDownPaymentApply = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('sale_order_down_payment_apply-delete')) {
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
