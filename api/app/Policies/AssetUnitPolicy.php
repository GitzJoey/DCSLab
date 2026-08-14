<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\AssetUnit;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AssetUnitPolicy
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

        if ($user->hasPermission('asset_unit-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?AssetUnit $assetUnit = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('asset_unit-read')) {
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

        if ($user->hasPermission('asset_unit-create')) {
            return true;
        }
    }

    public function update(User $user, ?AssetUnit $assetUnit = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('asset_unit-update')) {
            return true;
        }
    }

    public function delete(User $user, ?AssetUnit $assetUnit = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('asset_unit-delete')) {
            return true;
        }
    }

    public function restore(User $user, AssetUnit $assetUnit)
    {
        return false;
    }

    public function forceDelete(User $user, AssetUnit $assetUnit)
    {
        return false;
    }
}
