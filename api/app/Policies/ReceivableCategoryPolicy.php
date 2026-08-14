<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\ReceivableCategory;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReceivableCategoryPolicy
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

        if ($user->hasPermission('receivable_category-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?ReceivableCategory $receivableCategory = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('receivable_category-read')) {
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

        if ($user->hasPermission('receivable_category-create')) {
            return true;
        }
    }

    public function update(User $user, ?ReceivableCategory $receivableCategory = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('receivable_category-update')) {
            return true;
        }
    }

    public function delete(User $user, ?ReceivableCategory $receivableCategory = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('receivable_category-delete')) {
            return true;
        }
    }

    public function restore(User $user, ReceivableCategory $receivableCategory)
    {
        return false;
    }

    public function forceDelete(User $user, ReceivableCategory $receivableCategory)
    {
        return false;
    }
}
