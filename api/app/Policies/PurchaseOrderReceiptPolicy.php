<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\PurchaseOrderReceipt;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PurchaseOrderReceiptPolicy
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

        if ($user->hasPermission('purchase_order_receipt-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?PurchaseOrderReceipt $purchaseOrderReceipt = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchase_order_receipt-read')) {
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

        if ($user->hasPermission('purchase_order_receipt-create')) {
            return true;
        }
    }

    public function update(User $user, ?PurchaseOrderReceipt $purchaseOrderReceipt = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchase_order_receipt-update')) {
            return true;
        }
    }

    public function delete(User $user, ?PurchaseOrderReceipt $purchaseOrderReceipt = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchase_order_receipt-delete')) {
            return true;
        }
    }

    public function restore(User $user, PurchaseOrderReceipt $purchaseOrderReceipt)
    {
        return false;
    }

    public function forceDelete(User $user, PurchaseOrderReceipt $purchaseOrderReceipt)
    {
        return false;
    }
}
