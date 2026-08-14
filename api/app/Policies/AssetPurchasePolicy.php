<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\AssetPurchase;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AssetPurchasePolicy
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

        if ($user->hasPermission('asset_purchase-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?AssetPurchase $assetPurchase = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('asset_purchase-read')) {
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

        if ($user->hasPermission('asset_purchase-create')) {
            return true;
        }
    }

    public function update(User $user, ?AssetPurchase $assetPurchase = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('asset_purchase-update')) {
            return true;
        }
    }

    public function delete(User $user, ?AssetPurchase $assetPurchase = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('asset_purchase-delete')) {
            return true;
        }
    }

    public function restore(User $user, AssetPurchase $assetPurchase)
    {
        return false;
    }

    public function forceDelete(User $user, AssetPurchase $assetPurchase)
    {
        return false;
    }
}
