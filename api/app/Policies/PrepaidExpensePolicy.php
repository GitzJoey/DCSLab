<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\PrepaidExpense;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PrepaidExpensePolicy
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

        if ($user->hasPermission('prepaid_expense-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?PrepaidExpense $prepaidExpense = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('prepaid_expense-read')) {
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

        if ($user->hasPermission('prepaid_expense-create')) {
            return true;
        }
    }

    public function update(User $user, ?PrepaidExpense $prepaidExpense = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('prepaid_expense-update')) {
            return true;
        }
    }

    public function delete(User $user, ?PrepaidExpense $prepaidExpense = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('prepaid_expense-delete')) {
            return true;
        }
    }

    public function restore(User $user, PrepaidExpense $prepaidExpense)
    {
        return false;
    }

    public function forceDelete(User $user, PrepaidExpense $prepaidExpense)
    {
        return false;
    }
}
