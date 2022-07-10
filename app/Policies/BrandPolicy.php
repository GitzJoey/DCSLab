<?php

namespace App\Policies;

use App\Enums\UserRoles;
use App\Models\Brand;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BrandPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (!app()->environment('production') && $user->hasRole(UserRoles::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('brands-readAny')) {
            return true;
        }
    }

    public function view(User $user, Brand $brand = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (!app()->environment('production') && $user->hasRole(UserRoles::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('brands-read')) {
            return true;
        }
    }

    public function create(User $user)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (!app()->environment('production') && $user->hasRole(UserRoles::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('brands-create')) {
            return true;
        }
    }

    public function update(User $user, Brand $brand = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (!app()->environment('production') && $user->hasRole(UserRoles::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('brands-update')) {
            return true;
        }
    }

    public function delete(User $user, Brand $brand = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (!app()->environment('production') && $user->hasRole(UserRoles::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('brands-delete')) {
            return true;
        }
    }

    public function restore(User $user, Brand $brand)
    {
        return false;
    }

    public function forceDelete(User $user, Brand $brand)
    {
        return false;
    }
}
