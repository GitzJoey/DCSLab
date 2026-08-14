<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\AssetSale;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AssetSalePolicy
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

        if ($user->hasPermission('asset_sale-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?AssetSale $assetSale = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('asset_sale-read')) {
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

        if ($user->hasPermission('asset_sale-create')) {
            return true;
        }
    }

    public function update(User $user, ?AssetSale $assetSale = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('asset_sale-update')) {
            return true;
        }
    }

    public function delete(User $user, ?AssetSale $assetSale = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('asset_sale-delete')) {
            return true;
        }
    }

    public function restore(User $user, AssetSale $assetSale)
    {
        return false;
    }

    public function forceDelete(User $user, AssetSale $assetSale)
    {
        return false;
    }
}
