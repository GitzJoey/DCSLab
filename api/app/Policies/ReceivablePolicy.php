<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\Receivable;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReceivablePolicy
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

        if ($user->hasPermission('receivable-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?Receivable $receivable = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('receivable-read')) {
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

        if ($user->hasPermission('receivable-create')) {
            return true;
        }
    }

    public function update(User $user, ?Receivable $receivable = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('receivable-update')) {
            return true;
        }
    }

    public function delete(User $user, ?Receivable $receivable = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('receivable-delete')) {
            return true;
        }
    }

    public function restore(User $user, Receivable $receivable)
    {
        return false;
    }

    public function forceDelete(User $user, Receivable $receivable)
    {
        return false;
    }
}
