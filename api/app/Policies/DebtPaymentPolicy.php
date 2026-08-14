<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\DebtPayment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DebtPaymentPolicy
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

        if ($user->hasPermission('debt_payment-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?DebtPayment $debtPayment = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('debt_payment-read')) {
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

        if ($user->hasPermission('debt_payment-create')) {
            return true;
        }
    }

    public function update(User $user, ?DebtPayment $debtPayment = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('debt_payment-update')) {
            return true;
        }
    }

    public function delete(User $user, ?DebtPayment $debtPayment = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('debt_payment-delete')) {
            return true;
        }
    }

    public function restore(User $user, DebtPayment $debtPayment)
    {
        return false;
    }

    public function forceDelete(User $user, DebtPayment $debtPayment)
    {
        return false;
    }
}
