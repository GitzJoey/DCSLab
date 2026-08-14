<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\CustomerAddress;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerAddressPolicy
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

        if ($user->hasPermission('customer_address-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?CustomerAddress $customerAddress = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('customer_address-read')) {
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

        if ($user->hasPermission('customer_address-create')) {
            return true;
        }
    }

    public function update(User $user, ?CustomerAddress $customerAddress = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('customer_address-update')) {
            return true;
        }
    }

    public function delete(User $user, ?CustomerAddress $customerAddress = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('customer_address-delete')) {
            return true;
        }
    }

    public function restore(User $user, CustomerAddress $customerAddress)
    {
        return false;
    }

    public function forceDelete(User $user, CustomerAddress $customerAddress)
    {
        return false;
    }
}
