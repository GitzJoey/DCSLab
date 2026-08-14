<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\PrepaidIncomePayment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PrepaidIncomePaymentPolicy
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

        if ($user->hasPermission('prepaid_income_payment-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?PrepaidIncomePayment $prepaidIncomePayment = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('prepaid_income_payment-read')) {
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

        if ($user->hasPermission('prepaid_income_payment-create')) {
            return true;
        }
    }

    public function update(User $user, ?PrepaidIncomePayment $prepaidIncomePayment = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('prepaid_income_payment-update')) {
            return true;
        }
    }

    public function delete(User $user, ?PrepaidIncomePayment $prepaidIncomePayment = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('prepaid_income_payment-delete')) {
            return true;
        }
    }

    public function restore(User $user, PrepaidIncomePayment $prepaidIncomePayment)
    {
        return false;
    }

    public function forceDelete(User $user, PrepaidIncomePayment $prepaidIncomePayment)
    {
        return false;
    }
}
