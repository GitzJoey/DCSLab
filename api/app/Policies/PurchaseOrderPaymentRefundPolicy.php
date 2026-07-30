<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\PurchaseOrderPaymentRefund;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PurchaseOrderPaymentRefundPolicy
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

        if ($user->hasPermission('purchase_order_payment_refund-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?PurchaseOrderPaymentRefund $purchaseOrderPaymentRefund = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchase_order_payment_refund-read')) {
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

        if ($user->hasPermission('purchase_order_payment_refund-create')) {
            return true;
        }
    }

    public function update(User $user, ?PurchaseOrderPaymentRefund $purchaseOrderPaymentRefund = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchase_order_payment_refund-update')) {
            return true;
        }
    }

    public function delete(User $user, ?PurchaseOrderPaymentRefund $purchaseOrderPaymentRefund = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchase_order_payment_refund-delete')) {
            return true;
        }
    }

    public function restore(User $user, PurchaseOrderPaymentRefund $purchaseOrderPaymentRefund)
    {
        return false;
    }

    public function forceDelete(User $user, PurchaseOrderPaymentRefund $purchaseOrderPaymentRefund)
    {
        return false;
    }
}
