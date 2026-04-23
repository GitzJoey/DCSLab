<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\ReceivablePayment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReceivablePaymentPolicy
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

        if ($user->hasPermission('receivable_payment-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?ReceivablePayment $receivablePayment = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('receivable_payment-read')) {
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

        if ($user->hasPermission('receivable_payment-create')) {
            return true;
        }
    }

    public function update(User $user, ?ReceivablePayment $receivablePayment = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('receivable_payment-update')) {
            return true;
        }
    }

    public function delete(User $user, ?ReceivablePayment $receivablePayment = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('receivable_payment-delete')) {
            return true;
        }
    }

    public function restore(User $user, ReceivablePayment $receivablePayment)
    {
        return false;
    }

    public function forceDelete(User $user, ReceivablePayment $receivablePayment)
    {
        return false;
    }
}
