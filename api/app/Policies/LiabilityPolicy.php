<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\Liability;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LiabilityPolicy
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

        if ($user->hasPermission('liability-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?Liability $liability = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('liability-read')) {
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

        if ($user->hasPermission('liability-create')) {
            return true;
        }
    }

    public function update(User $user, ?Liability $liability = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('liability-update')) {
            return true;
        }
    }

    public function delete(User $user, ?Liability $liability = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('liability-delete')) {
            return true;
        }
    }

    public function restore(User $user, Liability $liability)
    {
        return false;
    }

    public function forceDelete(User $user, Liability $liability)
    {
        return false;
    }
}
