<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\PrepaidExpensePayment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PrepaidExpensePaymentPolicy
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

        if ($user->hasPermission('prepaid_expense_payment-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?PrepaidExpensePayment $prepaidExpensePayment = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('prepaid_expense_payment-read')) {
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

        if ($user->hasPermission('prepaid_expense_payment-create')) {
            return true;
        }
    }

    public function update(User $user, ?PrepaidExpensePayment $prepaidExpensePayment = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('prepaid_expense_payment-update')) {
            return true;
        }
    }

    public function delete(User $user, ?PrepaidExpensePayment $prepaidExpensePayment = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('prepaid_expense_payment-delete')) {
            return true;
        }
    }

    public function restore(User $user, PrepaidExpensePayment $prepaidExpensePayment)
    {
        return false;
    }

    public function forceDelete(User $user, PrepaidExpensePayment $prepaidExpensePayment)
    {
        return false;
    }
}
