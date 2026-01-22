<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\PurchaseOrderDownPayment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PurchaseOrderDownPaymentPolicy
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

        if ($user->hasPermission('purchaseOrderDownPayment-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?PurchaseOrderDownPayment $purchaseOrderDownPayment = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchaseOrderDownPayment-read')) {
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

        if ($user->hasPermission('purchaseOrderDownPayment-create')) {
            return true;
        }
    }

    public function update(User $user, ?PurchaseOrderDownPayment $purchaseOrderDownPayment = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchaseOrderDownPayment-update')) {
            return true;
        }
    }

    public function delete(User $user, ?PurchaseOrderDownPayment $purchaseOrderDownPayment = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('purchaseOrderDownPayment-delete')) {
            return true;
        }
    }

    public function restore(User $user, PurchaseOrderDownPayment $purchaseOrderDownPayment)
    {
        return false;
    }

    public function forceDelete(User $user, PurchaseOrderDownPayment $purchaseOrderDownPayment)
    {
        return false;
    }
}
