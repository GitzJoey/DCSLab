<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\NonCapitalWithdrawalCategory;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class NonCapitalWithdrawalCategoryPolicy
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

        if ($user->hasPermission('nonCapitalWithdrawalCategory-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?NonCapitalWithdrawalCategory $nonCapitalWithdrawalCategory = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('nonCapitalWithdrawalCategory-read')) {
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

        if ($user->hasPermission('nonCapitalWithdrawalCategory-create')) {
            return true;
        }
    }

    public function update(User $user, ?NonCapitalWithdrawalCategory $nonCapitalWithdrawalCategory = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('nonCapitalWithdrawalCategory-update')) {
            return true;
        }
    }

    public function delete(User $user, ?NonCapitalWithdrawalCategory $nonCapitalWithdrawalCategory = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('nonCapitalWithdrawalCategory-delete')) {
            return true;
        }
    }

    public function restore(User $user, NonCapitalWithdrawalCategory $nonCapitalWithdrawalCategory)
    {
        return false;
    }

    public function forceDelete(User $user, NonCapitalWithdrawalCategory $nonCapitalWithdrawalCategory)
    {
        return false;
    }
}
