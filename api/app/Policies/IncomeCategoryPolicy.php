<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\IncomeCategory;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class IncomeCategoryPolicy
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

        if ($user->hasPermission('income_category-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?IncomeCategory $incomeCategory = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('income_category-read')) {
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

        if ($user->hasPermission('income_category-create')) {
            return true;
        }
    }

    public function update(User $user, ?IncomeCategory $incomeCategory = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('income_category-update')) {
            return true;
        }
    }

    public function delete(User $user, ?IncomeCategory $incomeCategory = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('income_category-delete')) {
            return true;
        }
    }

    public function restore(User $user, IncomeCategory $incomeCategory)
    {
        return false;
    }

    public function forceDelete(User $user, IncomeCategory $incomeCategory)
    {
        return false;
    }
}
