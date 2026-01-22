<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\CashAccount;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CashAccountPolicy
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

        if ($user->hasPermission('cashAccount-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?CashAccount $cashAccount = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('cashAccount-read')) {
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

        if ($user->hasPermission('cashAccount-create')) {
            return true;
        }
    }

    public function update(User $user, ?CashAccount $cashAccount = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('cashAccount-update')) {
            return true;
        }
    }

    public function delete(User $user, ?CashAccount $cashAccount = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('cashAccount-delete')) {
            return true;
        }
    }

    public function restore(User $user, CashAccount $cashAccount)
    {
        return false;
    }

    public function forceDelete(User $user, CashAccount $cashAccount)
    {
        return false;
    }
}
