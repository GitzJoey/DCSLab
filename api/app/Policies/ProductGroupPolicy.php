<?php

namespace App\Policies;

use App\Enums\UserRoles;
use App\Models\ProductGroup;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductGroupPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRoles::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('productgroup-readAny')) {
            return true;
        }
    }

    public function view(User $user, ProductGroup $productGroup = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRoles::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('productgroup-read')) {
            return true;
        }
    }

    public function create(User $user)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRoles::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('productgroup-create')) {
            return true;
        }
    }

    public function update(User $user, ProductGroup $productGroup = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRoles::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('productgroup-update')) {
            return true;
        }
    }

    public function delete(User $user, ProductGroup $productGroup = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRoles::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('productgroup-delete')) {
            return true;
        }
    }

    public function restore(User $user, ProductGroup $productGroup)
    {
        return false;
    }

    public function forceDelete(User $user, ProductGroup $productGroup)
    {
        return false;
    }
}
