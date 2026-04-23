<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\DebtCategory;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DebtCategoryPolicy
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

        if ($user->hasPermission('debt_category-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?DebtCategory $debtCategory = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('debt_category-read')) {
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

        if ($user->hasPermission('debt_category-create')) {
            return true;
        }
    }

    public function update(User $user, ?DebtCategory $debtCategory = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('debt_category-update')) {
            return true;
        }
    }

    public function delete(User $user, ?DebtCategory $debtCategory = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('debt_category-delete')) {
            return true;
        }
    }

    public function restore(User $user, DebtCategory $debtCategory)
    {
        return false;
    }

    public function forceDelete(User $user, DebtCategory $debtCategory)
    {
        return false;
    }
}
