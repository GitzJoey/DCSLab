<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\CapitalOpening;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CapitalOpeningPolicy
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

        if ($user->hasPermission('capital_opening-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?CapitalOpening $capitalOpening = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('capital_opening-read')) {
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

        if ($user->hasPermission('capital_opening-create')) {
            return true;
        }
    }

    public function update(User $user, ?CapitalOpening $capitalOpening = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('capital_opening-update')) {
            return true;
        }
    }

    public function delete(User $user, ?CapitalOpening $capitalOpening = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('capital_opening-delete')) {
            return true;
        }
    }

    public function restore(User $user, CapitalOpening $capitalOpening)
    {
        return false;
    }

    public function forceDelete(User $user, CapitalOpening $capitalOpening)
    {
        return false;
    }
}
