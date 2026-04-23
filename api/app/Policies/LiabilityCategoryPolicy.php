<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\LiabilityCategory;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LiabilityCategoryPolicy
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

        if ($user->hasPermission('liability_category-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?LiabilityCategory $liabilityCategory = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('liability_category-read')) {
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

        if ($user->hasPermission('liability_category-create')) {
            return true;
        }
    }

    public function update(User $user, ?LiabilityCategory $liabilityCategory = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('liability_category-update')) {
            return true;
        }
    }

    public function delete(User $user, ?LiabilityCategory $liabilityCategory = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('liability_category-delete')) {
            return true;
        }
    }

    public function restore(User $user, LiabilityCategory $liabilityCategory)
    {
        return false;
    }

    public function forceDelete(User $user, LiabilityCategory $liabilityCategory)
    {
        return false;
    }
}
