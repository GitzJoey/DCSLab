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

        if ($user->hasPermission('brand-readAny')) {
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

        if ($user->hasPermission('brand-read')) {
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

        if ($user->hasPermission('brand-create')) {
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

        if ($user->hasPermission('brand-update')) {
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

        if ($user->hasPermission('brand-delete')) {
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
