<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\IncomePayment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class IncomePaymentPolicy
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

        if ($user->hasPermission('income_payment-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?IncomePayment $incomePayment = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('income_payment-read')) {
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

        if ($user->hasPermission('income_payment-create')) {
            return true;
        }
    }

    public function update(User $user, ?IncomePayment $incomePayment = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('income_payment-update')) {
            return true;
        }
    }

    public function delete(User $user, ?IncomePayment $incomePayment = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('income_payment-delete')) {
            return true;
        }
    }

    public function restore(User $user, IncomePayment $incomePayment)
    {
        return false;
    }

    public function forceDelete(User $user, IncomePayment $incomePayment)
    {
        return false;
    }
}
