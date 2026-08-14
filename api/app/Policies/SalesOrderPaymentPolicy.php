<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\SalesOrderPayment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SalesOrderPaymentPolicy
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

        if ($user->hasPermission('sales_order_payment-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?SalesOrderPayment $salesOrderPayment = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('sales_order_payment-read')) {
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

        if ($user->hasPermission('sales_order_payment-create')) {
            return true;
        }
    }

    public function update(User $user, ?SalesOrderPayment $salesOrderPayment = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('sales_order_payment-update')) {
            return true;
        }
    }

    public function delete(User $user, ?SalesOrderPayment $salesOrderPayment = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('sales_order_payment-delete')) {
            return true;
        }
    }

    public function restore(User $user, SalesOrderPayment $salesOrderPayment)
    {
        return false;
    }

    public function forceDelete(User $user, SalesOrderPayment $salesOrderPayment)
    {
        return false;
    }
}
