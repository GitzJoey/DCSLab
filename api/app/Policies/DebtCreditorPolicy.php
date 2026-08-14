<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\DebtCreditor;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DebtCreditorPolicy
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

        if ($user->hasPermission('debt_creditor-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?DebtCreditor $debt_creditor = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('debt_creditor-read')) {
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

        if ($user->hasPermission('debt_creditor-create')) {
            return true;
        }
    }

    public function update(User $user, ?DebtCreditor $debt_creditor = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('debt_creditor-update')) {
            return true;
        }
    }

    public function delete(User $user, ?DebtCreditor $debt_creditor = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('debt_creditor-delete')) {
            return true;
        }
    }

    public function restore(User $user, DebtCreditor $debt_creditor)
    {
        return false;
    }

    public function forceDelete(User $user, DebtCreditor $debt_creditor)
    {
        return false;
    }
}
