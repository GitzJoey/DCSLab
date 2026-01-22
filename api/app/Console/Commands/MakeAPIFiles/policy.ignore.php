<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\RepToPascalThis;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RepToPascalThisPolicy
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

        if ($user->hasPermission('RepToCamelThis-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?RepToPascalThis $RepToCamelThis = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('RepToCamelThis-read')) {
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

        if ($user->hasPermission('RepToCamelThis-create')) {
            return true;
        }
    }

    public function update(User $user, ?RepToPascalThis $RepToCamelThis = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('RepToCamelThis-update')) {
            return true;
        }
    }

    public function delete(User $user, ?RepToPascalThis $RepToCamelThis = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('RepToCamelThis-delete')) {
            return true;
        }
    }

    public function restore(User $user, RepToPascalThis $RepToCamelThis)
    {
        return false;
    }

    public function forceDelete(User $user, RepToPascalThis $RepToCamelThis)
    {
        return false;
    }
}
