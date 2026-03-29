<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\PurchaseOrderDownPaymentApply;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PurchaseOrderDownPaymentApplyPolicy
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

        if ($user->hasPermission('purchase_order_down_payment_apply-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?PurchaseOrderDownPaymentApply $purchaseOrderDownPaymentApply = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchase_order_down_payment_apply-read')) {
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

        if ($user->hasPermission('purchase_order_down_payment_apply-create')) {
            return true;
        }
    }

    public function update(User $user, ?PurchaseOrderDownPaymentApply $purchaseOrderDownPaymentApply = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchase_order_down_payment_apply-update')) {
            return true;
        }
    }

    public function delete(User $user, ?PurchaseOrderDownPaymentApply $purchaseOrderDownPaymentApply = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchase_order_down_payment_apply-delete')) {
            return true;
        }
    }

    public function restore(User $user, PurchaseOrderDownPaymentApply $purchaseOrderDownPaymentApply)
    {
        return false;
    }

    public function forceDelete(User $user, PurchaseOrderDownPaymentApply $purchaseOrderDownPaymentApply)
    {
        return false;
    }
}
