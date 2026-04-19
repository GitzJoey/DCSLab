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

        if ($user->hasPermission('purchase_additional_cost_category-readAny')) {
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

        if ($user->hasPermission('purchase_additional_cost_category-read')) {
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

        if ($user->hasPermission('purchase_additional_cost_category-create')) {
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

        if ($user->hasPermission('purchase_additional_cost_category-update')) {
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

        if ($user->hasPermission('purchase_additional_cost_category-delete')) {
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
