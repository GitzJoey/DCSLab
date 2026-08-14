<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\SalesInvoicePayment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SalesInvoicePaymentPolicy
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

        if ($user->hasPermission('sales_invoice_payment-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?SalesInvoicePayment $salesInvoicePayment = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('sales_invoice_payment-read')) {
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

        if ($user->hasPermission('sales_invoice_payment-create')) {
            return true;
        }
    }

    public function update(User $user, ?SalesInvoicePayment $salesInvoicePayment = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('sales_invoice_payment-update')) {
            return true;
        }
    }

    public function delete(User $user, ?SalesInvoicePayment $salesInvoicePayment = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('sales_invoice_payment-delete')) {
            return true;
        }
    }

    public function restore(User $user, SalesInvoicePayment $salesInvoicePayment)
    {
        return false;
    }

    public function forceDelete(User $user, SalesInvoicePayment $salesInvoicePayment)
    {
        return false;
    }
}
