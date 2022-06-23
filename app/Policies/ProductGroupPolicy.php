<?php

namespace App\Policies;

use App\Models\User;
use App\Enums\UserRoles;
use App\Models\ProductGroup;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductGroupPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        if ($user->roles->isEmpty()) return false;

        if ($user->hasRole(UserRoles::DEVELOPER->value)) return true;

        if ($user->hasPermission('productGroup-read')) return true;
    }

    public function view(User $user, ProductGroup $productGroup = null)
    {
        return $this->viewAny($user);
    }

    public function create(User $user)
    {
        if ($user->roles->isEmpty()) return false;

        if ($user->hasRole(UserRoles::DEVELOPER->value)) return true;

        if ($user->hasPermission('productGroup-create')) return true;
    }

    public function update(User $user, ProductGroup $productGroup = null)
    {
        if ($user->roles->isEmpty()) return false;

        if ($user->hasRole(UserRoles::DEVELOPER->value)) return true;
    
        if ($user->hasPermission('productGroup-update')) return true;
    }

    public function delete(User $user, ProductGroup $productGroup = null)
    {
        if ($user->roles->isEmpty()) return false;

        if ($user->hasRole(UserRoles::DEVELOPER->value)) return true;
    
        if ($user->hasPermission('productGroup-delete')) return true;
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