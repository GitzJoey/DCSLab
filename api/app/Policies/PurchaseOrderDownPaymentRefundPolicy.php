<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\PurchaseOrderDownPaymentRefund;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PurchaseOrderDownPaymentRefundPolicy
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

        if ($user->hasPermission('purchase_order_down_payment_refund-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?PurchaseOrderDownPaymentRefund $purchaseOrderDownPaymentRefund = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchase_order_down_payment_refund-read')) {
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

        if ($user->hasPermission('purchase_order_down_payment_refund-create')) {
            return true;
        }
    }

    public function update(User $user, ?PurchaseOrderDownPaymentRefund $purchaseOrderDownPaymentRefund = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchase_order_down_payment_refund-update')) {
            return true;
        }
    }

    public function delete(User $user, ?PurchaseOrderDownPaymentRefund $purchaseOrderDownPaymentRefund = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchase_order_down_payment_refund-delete')) {
            return true;
        }
    }

    public function restore(User $user, PurchaseOrderDownPaymentRefund $purchaseOrderDownPaymentRefund)
    {
        return false;
    }

    public function forceDelete(User $user, PurchaseOrderDownPaymentRefund $purchaseOrderDownPaymentRefund)
    {
        return false;
    }
}
