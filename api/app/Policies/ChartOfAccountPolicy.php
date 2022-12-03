<?php

namespace App\Policies;

use App\Enums\UserRoles;
use App\Models\ChartOfAccount;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ChartOfAccountPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (!app()->environment('production') && $user->hasRole(UserRoles::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('chartofaccount-readAny')) {
            return true;
        }
    }

    public function view(User $user, ChartOfAccount $chartOfAccount = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (!app()->environment('production') && $user->hasRole(UserRoles::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('chartofaccount-read')) {
            return true;
        }
    }

    public function create(User $user)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (!app()->environment('production') && $user->hasRole(UserRoles::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('chartofaccount-create')) {
            return true;
        }
    }

    public function update(User $user, ChartOfAccount $chartOfAccount = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (!app()->environment('production') && $user->hasRole(UserRoles::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('chartofaccount-update')) {
            return true;
        }
    }

    public function delete(User $user, ChartOfAccount $chartOfAccount = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (!app()->environment('production') && $user->hasRole(UserRoles::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('chartofaccount-delete')) {
            return true;
        }
    }

    public function restore(User $user, ChartOfAccount $chartOfAccount)
    {
        return false;
    }

    public function forceDelete(User $user, ChartOfAccount $chartOfAccount)
    {
        return false;
    }
}
