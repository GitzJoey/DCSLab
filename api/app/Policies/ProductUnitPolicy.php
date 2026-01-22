<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\ProductUnit;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductUnitPolicy
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

        if ($user->hasPermission('productUnit-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?ProductUnit $productUnit = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('productUnit-read')) {
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

        if ($user->hasPermission('productUnit-create')) {
            return true;
        }
    }

    public function update(User $user, ?ProductUnit $productUnit = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('productUnit-update')) {
            return true;
        }
    }

    public function delete(User $user, ?ProductUnit $productUnit = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('productUnit-delete')) {
            return true;
        }
    }

    public function restore(User $user, ProductUnit $productUnit)
    {
        return false;
    }

    public function forceDelete(User $user, ProductUnit $productUnit)
    {
        return false;
    }
}
