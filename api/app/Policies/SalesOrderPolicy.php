<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SalesOrderPolicy
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

        if ($user->hasPermission('salesOrder-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?SalesOrder $salesOrder = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('salesOrder-read')) {
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

        if ($user->hasPermission('salesOrder-create')) {
            return true;
        }
    }

    public function update(User $user, ?SalesOrder $salesOrder = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('salesOrder-update')) {
            return true;
        }
    }

    public function delete(User $user, ?SalesOrder $salesOrder = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('salesOrder-delete')) {
            return true;
        }
    }

    public function restore(User $user, SalesOrder $salesOrder)
    {
        return false;
    }

    public function forceDelete(User $user, SalesOrder $salesOrder)
    {
        return false;
    }
}
