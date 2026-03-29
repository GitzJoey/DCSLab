<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\NonCapitalWithdrawal;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class NonCapitalWithdrawalPolicy
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

        if ($user->hasPermission('non_capital_withdrawal-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?NonCapitalWithdrawal $nonCapitalWithdrawal = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('non_capital_withdrawal-read')) {
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

        if ($user->hasPermission('non_capital_withdrawal-create')) {
            return true;
        }
    }

    public function update(User $user, ?NonCapitalWithdrawal $nonCapitalWithdrawal = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('non_capital_withdrawal-update')) {
            return true;
        }
    }

    public function delete(User $user, ?NonCapitalWithdrawal $nonCapitalWithdrawal = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('non_capital_withdrawal-delete')) {
            return true;
        }
    }

    public function restore(User $user, NonCapitalWithdrawal $nonCapitalWithdrawal)
    {
        return false;
    }

    public function forceDelete(User $user, NonCapitalWithdrawal $nonCapitalWithdrawal)
    {
        return false;
    }
}
