<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\AssetCategory;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AssetCategoryPolicy
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

        if ($user->hasPermission('asset_category-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?AssetCategory $assetCategory = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('asset_category-read')) {
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

        if ($user->hasPermission('asset_category-create')) {
            return true;
        }
    }

    public function update(User $user, ?AssetCategory $assetCategory = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('asset_category-update')) {
            return true;
        }
    }

    public function delete(User $user, ?AssetCategory $assetCategory = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('asset_category-delete')) {
            return true;
        }
    }

    public function restore(User $user, AssetCategory $assetCategory)
    {
        return false;
    }

    public function forceDelete(User $user, AssetCategory $assetCategory)
    {
        return false;
    }
}
