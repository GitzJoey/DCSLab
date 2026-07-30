<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\SalesOrderDelivery;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SalesOrderDeliveryPolicy
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

        if ($user->hasPermission('sales_order_delivery-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?SalesOrderDelivery $salesOrderDelivery = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('sales_order_delivery-read')) {
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

        if ($user->hasPermission('sales_order_delivery-create')) {
            return true;
        }
    }

    public function update(User $user, ?SalesOrderDelivery $salesOrderDelivery = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('sales_order_delivery-update')) {
            return true;
        }
    }

    public function delete(User $user, ?SalesOrderDelivery $salesOrderDelivery = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('sales_order_delivery-delete')) {
            return true;
        }
    }

    public function restore(User $user, SalesOrderDelivery $salesOrderDelivery)
    {
        return false;
    }

    public function forceDelete(User $user, SalesOrderDelivery $salesOrderDelivery)
    {
        return false;
    }
}
