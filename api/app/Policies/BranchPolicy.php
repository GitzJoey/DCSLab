<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

class BranchPolicy
{
    public function __construct()
    {
        //
    }

    public function before(User $user): ?bool
    {
        if (! app()->isProduction() && $user->hasRole(UserRole::DEVELOPER->value)) {
            return true;
        }

        return null;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('branch-create');
    }

    public function view(User $user): bool
    {
        return $user->hasPermission('branch-read');
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('branch-readAny');
    }

    public function update(User $user): bool
    {
        return $user->hasPermission('branch-update');
    }

    public function delete(User $user): bool
    {
        return $user->hasPermission('branch-delete');
    }

    public function restore(User $user): bool
    {
        return $user->hasPermission('branch-restore');
    }

    public function authorizeCreate(User $user): bool
    {
        return $user->hasPermission('branch-authorizeCreate');
    }

    public function authorizeUpdate(User $user): bool
    {
        return $user->hasPermission('branch-authorizeUpdate');
    }

    public function authorizeDelete(User $user): bool
    {
        return $user->hasPermission('branch-authorizeDelete');
    }

    public function authorizeRestore(User $user): bool
    {
        return $user->hasPermission('branch-authorizeRestore');
    }
}
