<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\SalesInvoice;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SalesInvoicePolicy
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

        if ($user->hasPermission('sales_invoice-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?SalesInvoice $salesInvoice = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('sales_invoice-read')) {
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

        if ($user->hasPermission('sales_invoice-create')) {
            return true;
        }
    }

    public function update(User $user, ?SalesInvoice $salesInvoice = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('sales_invoice-update')) {
            return true;
        }
    }

    public function delete(User $user, ?SalesInvoice $salesInvoice = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('sales_invoice-delete')) {
            return true;
        }
    }

    public function restore(User $user, SalesInvoice $salesInvoice)
    {
        return false;
    }

    public function forceDelete(User $user, SalesInvoice $salesInvoice)
    {
        return false;
    }
}
