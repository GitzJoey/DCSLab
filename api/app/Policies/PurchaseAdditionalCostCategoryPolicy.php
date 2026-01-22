<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\PurchaseAdditionalCostCategory;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PurchaseAdditionalCostCategoryPolicy
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

        if ($user->hasPermission('purchaseAdditionalCostCategory-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?PurchaseAdditionalCostCategory $purchaseAdditionalCostCategory = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchaseAdditionalCostCategory-read')) {
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

        if ($user->hasPermission('purchaseAdditionalCostCategory-create')) {
            return true;
        }
    }

    public function update(User $user, ?PurchaseAdditionalCostCategory $purchaseAdditionalCostCategory = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchaseAdditionalCostCategory-update')) {
            return true;
        }
    }

    public function delete(User $user, ?PurchaseAdditionalCostCategory $purchaseAdditionalCostCategory = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchaseAdditionalCostCategory-delete')) {
            return true;
        }
    }

    public function restore(User $user, PurchaseAdditionalCostCategory $purchaseAdditionalCostCategory)
    {
        return false;
    }

    public function forceDelete(User $user, PurchaseAdditionalCostCategory $purchaseAdditionalCostCategory)
    {
        return false;
    }
}
