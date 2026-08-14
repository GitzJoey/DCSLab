<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\AssetAdjustment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AssetAdjustmentPolicy
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

        if ($user->hasPermission('asset_adjustment-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?AssetAdjustment $assetAdjustment = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('asset_adjustment-read')) {
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

        if ($user->hasPermission('asset_adjustment-create')) {
            return true;
        }
    }

    public function update(User $user, ?AssetAdjustment $assetAdjustment = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('asset_adjustment-update')) {
            return true;
        }
    }

    public function delete(User $user, ?AssetAdjustment $assetAdjustment = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('asset_adjustment-delete')) {
            return true;
        }
    }

    public function restore(User $user, AssetAdjustment $assetAdjustment)
    {
        return false;
    }

    public function forceDelete(User $user, AssetAdjustment $assetAdjustment)
    {
        return false;
    }
}
