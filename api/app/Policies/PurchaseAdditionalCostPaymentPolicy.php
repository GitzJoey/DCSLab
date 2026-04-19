<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\PurchaseAdditionalCostPayment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PurchaseAdditionalCostPaymentPolicy
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

        if ($user->hasPermission('purchase_additional_cost_payment-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?PurchaseAdditionalCostPayment $purchaseAdditionalCostPayment = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchase_additional_cost_payment-read')) {
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

        if ($user->hasPermission('purchase_additional_cost_payment-create')) {
            return true;
        }
    }

    public function update(User $user, ?PurchaseAdditionalCostPayment $purchaseAdditionalCostPayment = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchase_additional_cost_payment-update')) {
            return true;
        }
    }

    public function delete(User $user, ?PurchaseAdditionalCostPayment $purchaseAdditionalCostPayment = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchase_additional_cost_payment-delete')) {
            return true;
        }
    }

    public function restore(User $user, PurchaseAdditionalCostPayment $purchaseAdditionalCostPayment)
    {
        return false;
    }

    public function forceDelete(User $user, PurchaseAdditionalCostPayment $purchaseAdditionalCostPayment)
    {
        return false;
    }
}
