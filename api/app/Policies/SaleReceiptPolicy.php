<?php

namespace App\Policies;

use App\Enums\UserRoles;
use App\Models\SaleReceipt;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SaleReceiptPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRoles::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('saleReceipt-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?SaleReceipt $saleReceipt = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRoles::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('saleReceipt-read')) {
            return true;
        }
    }

    public function create(User $user)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRoles::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('saleReceipt-create')) {
            return true;
        }
    }

    public function update(User $user, ?SaleReceipt $saleReceipt = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRoles::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('saleReceipt-update')) {
            return true;
        }
    }

    public function delete(User $user, ?SaleReceipt $saleReceipt = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRoles::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('saleReceipt-delete')) {
            return true;
        }
    }

    public function restore(User $user, SaleReceipt $saleReceipt)
    {
        return false;
    }

    public function forceDelete(User $user, SaleReceipt $saleReceipt)
    {
        return false;
    }
}
