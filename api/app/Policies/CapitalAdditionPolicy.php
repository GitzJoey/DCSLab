<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\CapitalAddition;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CapitalAdditionPolicy
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

        if ($user->hasPermission('capitalAddition-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?CapitalAddition $capitalAddition = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('capitalAddition-read')) {
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

        if ($user->hasPermission('capitalAddition-create')) {
            return true;
        }
    }

    public function update(User $user, ?CapitalAddition $capitalAddition = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('capitalAddition-update')) {
            return true;
        }
    }

    public function delete(User $user, ?CapitalAddition $capitalAddition = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('capitalAddition-delete')) {
            return true;
        }
    }

    public function restore(User $user, CapitalAddition $capitalAddition)
    {
        return false;
    }

    public function forceDelete(User $user, CapitalAddition $capitalAddition)
    {
        return false;
    }
}
