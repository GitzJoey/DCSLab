<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\ExpensePayment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ExpensePaymentPolicy
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

        if ($user->hasPermission('expense_payment-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?ExpensePayment $expensePayment = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('expense_payment-read')) {
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

        if ($user->hasPermission('expense_payment-create')) {
            return true;
        }
    }

    public function update(User $user, ?ExpensePayment $expensePayment = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('expense_payment-update')) {
            return true;
        }
    }

    public function delete(User $user, ?ExpensePayment $expensePayment = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('expense_payment-delete')) {
            return true;
        }
    }

    public function restore(User $user, ExpensePayment $expensePayment)
    {
        return false;
    }

    public function forceDelete(User $user, ExpensePayment $expensePayment)
    {
        return false;
    }
}
