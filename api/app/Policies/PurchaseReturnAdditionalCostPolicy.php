<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\PurchaseReturnAdditionalCost;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PurchaseReturnAdditionalCostPolicy
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

        if ($user->hasPermission('purchase_return_additional_cost-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?PurchaseReturnAdditionalCost $purchaseReturnAdditionalCost = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchase_return_additional_cost-read')) {
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

        if ($user->hasPermission('purchase_return_additional_cost-create')) {
            return true;
        }
    }

    public function update(User $user, ?PurchaseReturnAdditionalCost $purchaseReturnAdditionalCost = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchase_return_additional_cost-update')) {
            return true;
        }
    }

    public function delete(User $user, ?PurchaseReturnAdditionalCost $purchaseReturnAdditionalCost = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchase_return_additional_cost-delete')) {
            return true;
        }
    }

    public function restore(User $user, PurchaseReturnAdditionalCost $purchaseReturnAdditionalCost)
    {
        return false;
    }

    public function forceDelete(User $user, PurchaseReturnAdditionalCost $purchaseReturnAdditionalCost)
    {
        return false;
    }
}
