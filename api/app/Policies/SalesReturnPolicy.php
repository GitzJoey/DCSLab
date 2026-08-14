<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\SalesReturn;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SalesReturnPolicy
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

        if ($user->hasPermission('sales_return-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?SalesReturn $salesReturn = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('sales_return-read')) {
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

        if ($user->hasPermission('sales_return-create')) {
            return true;
        }
    }

    public function update(User $user, ?SalesReturn $salesReturn = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('sales_return-update')) {
            return true;
        }
    }

    public function delete(User $user, ?SalesReturn $salesReturn = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('sales_return-delete')) {
            return true;
        }
    }

    public function restore(User $user, SalesReturn $salesReturn)
    {
        return false;
    }

    public function forceDelete(User $user, SalesReturn $salesReturn)
    {
        return false;
    }
}
