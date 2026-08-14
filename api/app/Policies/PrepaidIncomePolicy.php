<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\PrepaidIncome;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PrepaidIncomePolicy
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

        if ($user->hasPermission('prepaid_income-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?PrepaidIncome $prepaidIncome = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('prepaid_income-read')) {
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

        if ($user->hasPermission('prepaid_income-create')) {
            return true;
        }
    }

    public function update(User $user, ?PrepaidIncome $prepaidIncome = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('prepaid_income-update')) {
            return true;
        }
    }

    public function delete(User $user, ?PrepaidIncome $prepaidIncome = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('prepaid_income-delete')) {
            return true;
        }
    }

    public function restore(User $user, PrepaidIncome $prepaidIncome)
    {
        return false;
    }

    public function forceDelete(User $user, PrepaidIncome $prepaidIncome)
    {
        return false;
    }
}
