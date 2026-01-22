<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\PurchaseReturnAdditionalCostCategory;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PurchaseReturnAdditionalCostCategoryPolicy
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

        if ($user->hasPermission('purchaseReturnAdditionalCostCategory-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?PurchaseReturnAdditionalCostCategory $purchaseReturnAdditionalCostCategory = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchaseReturnAdditionalCostCategory-read')) {
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

        if ($user->hasPermission('purchaseReturnAdditionalCostCategory-create')) {
            return true;
        }
    }

    public function update(User $user, ?PurchaseReturnAdditionalCostCategory $purchaseReturnAdditionalCostCategory = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchaseReturnAdditionalCostCategory-update')) {
            return true;
        }
    }

    public function delete(User $user, ?PurchaseReturnAdditionalCostCategory $purchaseReturnAdditionalCostCategory = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchaseReturnAdditionalCostCategory-delete')) {
            return true;
        }
    }

    public function restore(User $user, PurchaseReturnAdditionalCostCategory $purchaseReturnAdditionalCostCategory)
    {
        return false;
    }

    public function forceDelete(User $user, PurchaseReturnAdditionalCostCategory $purchaseReturnAdditionalCostCategory)
    {
        return false;
    }
}
