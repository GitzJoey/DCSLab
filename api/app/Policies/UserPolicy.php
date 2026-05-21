<?php

namespace App\Policies;

use App\Models\User;
use App\Enums\UserRole;

class UserPolicy
{
    public function __construct()
    {
        //
    }

    public function before (User $user): ?bool
    {
        if (! app()->isProduction() && $user->hasRole(UserRole::DEVELOPER->value)) {
            return true;
        }

        return null;
    }

    public function create (User $user): bool
    {
        return $user->hasPermission('user-create');
    }

    public function view (User $user): bool
    {
        return $user->hasPermission('user-read');
    }

    public function viewAny (User $user): bool
    {
        return $user->hasPermission('user-readAny');
    }

    public function update (User $user): bool
    {
        return $user->hasPermission('user-update');
    }

    public function delete (User $user): bool
    {
        return $user->hasPermission('user-delete');
    }

    public function restore (User $user): bool
    {
        return $user->hasPermission('user-restore');
    }

    public function authorizeCreate (User $user): bool
    {
        return $user->hasPermission('user-authorizeCreate');
    }

    public function authorizeUpdate (User $user): bool
    {
        return $user->hasPermission('user-authorizeUpdate');
    }

    public function authorizeDelete (User $user): bool
    {
        return $user->hasPermission('user-authorizeDelete');
    }

    public function authorizeRestore (User $user): bool
    {
        return $user->hasPermission('user-authorizeRestore');
    }
}
