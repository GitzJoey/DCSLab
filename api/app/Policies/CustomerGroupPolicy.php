<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\CustomerGroup;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerGroupPolicy
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

        if ($user->hasPermission('customer_group-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?CustomerGroup $customerGroup = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('customer_group-read')) {
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

        if ($user->hasPermission('customer_group-create')) {
            return true;
        }
    }

    public function update(User $user, ?CustomerGroup $customerGroup = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('customer_group-update')) {
            return true;
        }
    }

    public function delete(User $user, ?CustomerGroup $customerGroup = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('customer_group-delete')) {
            return true;
        }
    }

    public function restore(User $user, CustomerGroup $customerGroup)
    {
        return false;
    }

    public function forceDelete(User $user, CustomerGroup $customerGroup)
    {
        return false;
    }
}
