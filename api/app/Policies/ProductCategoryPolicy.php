<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductCategoryPolicy
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

        if ($user->hasPermission('product_category-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?ProductCategory $productCategory = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('product_category-read')) {
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

        if ($user->hasPermission('product_category-create')) {
            return true;
        }
    }

    public function update(User $user, ?ProductCategory $productCategory = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('product_category-update')) {
            return true;
        }
    }

    public function delete(User $user, ?ProductCategory $productCategory = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('product_category-delete')) {
            return true;
        }
    }

    public function restore(User $user, ProductCategory $productCategory)
    {
        return false;
    }

    public function forceDelete(User $user, ProductCategory $productCategory)
    {
        return false;
    }
}
