<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\PurchasePayment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PurchasePaymentPolicy
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

        if ($user->hasPermission('purchasePayment-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?PurchasePayment $purchasePayment = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchasePayment-read')) {
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

        if ($user->hasPermission('purchasePayment-create')) {
            return true;
        }
    }

    public function update(User $user, ?PurchasePayment $purchasePayment = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchasePayment-update')) {
            return true;
        }
    }

    public function delete(User $user, ?PurchasePayment $purchasePayment = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchasePayment-delete')) {
            return true;
        }
    }

    public function restore(User $user, PurchasePayment $purchasePayment)
    {
        return false;
    }

    public function forceDelete(User $user, PurchasePayment $purchasePayment)
    {
        return false;
    }
}
