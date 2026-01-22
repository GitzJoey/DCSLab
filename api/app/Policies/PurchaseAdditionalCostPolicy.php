<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\PurchaseAdditionalCost;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PurchaseAdditionalCostPolicy
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

        if ($user->hasPermission('purchaseAdditionalCost-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?PurchaseAdditionalCost $purchaseAdditionalCost = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchaseAdditionalCost-read')) {
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

        if ($user->hasPermission('purchaseAdditionalCost-create')) {
            return true;
        }
    }

    public function update(User $user, ?PurchaseAdditionalCost $purchaseAdditionalCost = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchaseAdditionalCost-update')) {
            return true;
        }
    }

    public function delete(User $user, ?PurchaseAdditionalCost $purchaseAdditionalCost = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchaseAdditionalCost-delete')) {
            return true;
        }
    }

    public function restore(User $user, PurchaseAdditionalCost $purchaseAdditionalCost)
    {
        return false;
    }

    public function forceDelete(User $user, PurchaseAdditionalCost $purchaseAdditionalCost)
    {
        return false;
    }
}
