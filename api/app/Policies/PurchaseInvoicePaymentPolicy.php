<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\PurchaseInvoicePayment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PurchaseInvoicePaymentPolicy
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

        if ($user->hasPermission('purchase_invoice_payment-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?PurchaseInvoicePayment $purchaseInvoicePayment = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchase_invoice_payment-read')) {
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

        if ($user->hasPermission('purchase_invoice_payment-create')) {
            return true;
        }
    }

    public function update(User $user, ?PurchaseInvoicePayment $purchaseInvoicePayment = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchase_invoice_payment-update')) {
            return true;
        }
    }

    public function delete(User $user, ?PurchaseInvoicePayment $purchaseInvoicePayment = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchase_invoice_payment-delete')) {
            return true;
        }
    }

    public function restore(User $user, PurchaseInvoicePayment $purchaseInvoicePayment)
    {
        return false;
    }

    public function forceDelete(User $user, PurchaseInvoicePayment $purchaseInvoicePayment)
    {
        return false;
    }
}
