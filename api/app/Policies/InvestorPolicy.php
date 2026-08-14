<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\Investor;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvestorPolicy
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

        if ($user->hasPermission('investor-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?Investor $investor = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('investor-read')) {
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

        if ($user->hasPermission('investor-create')) {
            return true;
        }
    }

    public function update(User $user, ?Investor $investor = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('investor-update')) {
            return true;
        }
    }

    public function delete(User $user, ?Investor $investor = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('investor-delete')) {
            return true;
        }
    }

    public function restore(User $user, Investor $investor)
    {
        return false;
    }

    public function forceDelete(User $user, Investor $investor)
    {
        return false;
    }
}
