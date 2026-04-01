<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\CapitalTransaction;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CapitalTransactionPolicy
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

        if ($user->hasPermission('capital_transaction-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?CapitalTransaction $capitalTransaction = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('capital_transaction-read')) {
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

        if ($user->hasPermission('capital_transaction-create')) {
            return true;
        }
    }

    public function update(User $user, ?CapitalTransaction $capitalTransaction = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('capital_transaction-update')) {
            return true;
        }
    }

    public function delete(User $user, ?CapitalTransaction $capitalTransaction = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('capital_transaction-delete')) {
            return true;
        }
    }

    public function restore(User $user, CapitalTransaction $capitalTransaction)
    {
        return false;
    }

    public function forceDelete(User $user, CapitalTransaction $capitalTransaction)
    {
        return false;
    }
}
