<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\NonCapitalAdditionCategory;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class NonCapitalAdditionCategoryPolicy
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

        if ($user->hasPermission('nonCapitalAdditionCategory-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?NonCapitalAdditionCategory $nonCapitalAdditionCategory = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('nonCapitalAdditionCategory-read')) {
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

        if ($user->hasPermission('nonCapitalAdditionCategory-create')) {
            return true;
        }
    }

    public function update(User $user, ?NonCapitalAdditionCategory $nonCapitalAdditionCategory = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('nonCapitalAdditionCategory-update')) {
            return true;
        }
    }

    public function delete(User $user, ?NonCapitalAdditionCategory $nonCapitalAdditionCategory = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('nonCapitalAdditionCategory-delete')) {
            return true;
        }
    }

    public function restore(User $user, NonCapitalAdditionCategory $nonCapitalAdditionCategory)
    {
        return false;
    }

    public function forceDelete(User $user, NonCapitalAdditionCategory $nonCapitalAdditionCategory)
    {
        return false;
    }
}
