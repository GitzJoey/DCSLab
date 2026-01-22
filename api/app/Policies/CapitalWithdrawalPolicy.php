<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\CapitalWithdrawal;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CapitalWithdrawalPolicy
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

        if ($user->hasPermission('capitalWithdrawal-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?CapitalWithdrawal $capitalWithdrawal = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('capitalWithdrawal-read')) {
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

        if ($user->hasPermission('capitalWithdrawal-create')) {
            return true;
        }
    }

    public function update(User $user, ?CapitalWithdrawal $capitalWithdrawal = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('capitalWithdrawal-update')) {
            return true;
        }
    }

    public function delete(User $user, ?CapitalWithdrawal $capitalWithdrawal = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('capitalWithdrawal-delete')) {
            return true;
        }
    }

    public function restore(User $user, CapitalWithdrawal $capitalWithdrawal)
    {
        return false;
    }

    public function forceDelete(User $user, CapitalWithdrawal $capitalWithdrawal)
    {
        return false;
    }
}
