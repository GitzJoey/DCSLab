<?php

namespace App\Policies;

use App\Models\User;
use App\Enums\UserRole;

class ProfilePolicy
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
        return $user->hasPermission('profile-create');
    }

    public function view (User $user): bool
    {
        return $user->hasPermission('profile-read');
    }

    public function viewAny (User $user): bool
    {
        return $user->hasPermission('profile-readAny');
    }

    public function update (User $user): bool
    {
        return $user->hasPermission('profile-update');
    }

    public function delete (User $user): bool
    {
        return $user->hasPermission('profile-delete');
    }

    public function restore (User $user): bool
    {
        return $user->hasPermission('profile-restore');
    }

    public function authorizeCreate (User $user): bool
    {
        return $user->hasPermission('profile-authorizeCreate');
    }

    public function authorizeUpdate (User $user): bool
    {
        return $user->hasPermission('profile-authorizeUpdate');
    }

    public function authorizeDelete (User $user): bool
    {
        return $user->hasPermission('profile-authorizeDelete');
    }

    public function authorizeRestore (User $user): bool
    {
        return $user->hasPermission('profile-authorizeRestore');
    }
}
