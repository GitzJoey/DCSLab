<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\NonCapitalAddition;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class NonCapitalAdditionPolicy
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

        if ($user->hasPermission('nonCapitalAddition-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?NonCapitalAddition $nonCapitalAddition = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('nonCapitalAddition-read')) {
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

        if ($user->hasPermission('nonCapitalAddition-create')) {
            return true;
        }
    }

    public function update(User $user, ?NonCapitalAddition $nonCapitalAddition = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('nonCapitalAddition-update')) {
            return true;
        }
    }

    public function delete(User $user, ?NonCapitalAddition $nonCapitalAddition = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('nonCapitalAddition-delete')) {
            return true;
        }
    }

    public function restore(User $user, NonCapitalAddition $nonCapitalAddition)
    {
        return false;
    }

    public function forceDelete(User $user, NonCapitalAddition $nonCapitalAddition)
    {
        return false;
    }
}
