<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\Debt;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DebtPolicy
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

        if ($user->hasPermission('debt-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?Debt $debt = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('debt-read')) {
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

        if ($user->hasPermission('debt-create')) {
            return true;
        }
    }

    public function update(User $user, ?Debt $debt = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('debt-update')) {
            return true;
        }
    }

    public function delete(User $user, ?Debt $debt = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('debt-delete')) {
            return true;
        }
    }

    public function restore(User $user, Debt $debt)
    {
        return false;
    }

    public function forceDelete(User $user, Debt $debt)
    {
        return false;
    }
}
