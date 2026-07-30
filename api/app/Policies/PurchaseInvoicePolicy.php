<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\PurchaseInvoice;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PurchaseInvoicePolicy
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

        if ($user->hasPermission('purchase_invoice-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?PurchaseInvoice $purchaseInvoice = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchase_invoice-read')) {
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

        if ($user->hasPermission('purchase_invoice-create')) {
            return true;
        }
    }

    public function update(User $user, ?PurchaseInvoice $purchaseInvoice = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchase_invoice-update')) {
            return true;
        }
    }

    public function delete(User $user, ?PurchaseInvoice $purchaseInvoice = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchase_invoice-delete')) {
            return true;
        }
    }

    public function restore(User $user, PurchaseInvoice $purchaseInvoice)
    {
        return false;
    }

    public function forceDelete(User $user, PurchaseInvoice $purchaseInvoice)
    {
        return false;
    }
}
