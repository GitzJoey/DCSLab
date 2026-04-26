<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\PurchaseReceipt;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PurchaseReceiptPolicy
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

        if ($user->hasPermission('purchase-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?PurchaseReceipt $purchaseReceipt = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchase-read')) {
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

        if ($user->hasPermission('purchase-create')) {
            return true;
        }
    }

    public function update(User $user, ?PurchaseReceipt $purchaseReceipt = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchase-update')) {
            return true;
        }
    }

    public function delete(User $user, ?PurchaseReceipt $purchaseReceipt = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchase-delete')) {
            return true;
        }
    }

    public function restore(User $user, PurchaseReceipt $purchaseReceipt)
    {
        return false;
    }

    public function forceDelete(User $user, PurchaseReceipt $purchaseReceipt)
    {
        return false;
    }
}
