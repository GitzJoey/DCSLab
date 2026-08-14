<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ExpenseCategoryPolicy
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

        if ($user->hasPermission('expense_category-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?ExpenseCategory $expenseCategory = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('expense_category-read')) {
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

        if ($user->hasPermission('expense_category-create')) {
            return true;
        }
    }

    public function update(User $user, ?ExpenseCategory $expenseCategory = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('expense_category-update')) {
            return true;
        }
    }

    public function delete(User $user, ?ExpenseCategory $expenseCategory = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('expense_category-delete')) {
            return true;
        }
    }

    public function restore(User $user, ExpenseCategory $expenseCategory)
    {
        return false;
    }

    public function forceDelete(User $user, ExpenseCategory $expenseCategory)
    {
        return false;
    }
}
