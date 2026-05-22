<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

class CompanyPolicy
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
        return $user->hasPermission('company-create');
    }

    public function view(User $user): bool
    {
        return $user->hasPermission('company-read');
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('company-readAny');
    }

    public function update(User $user): bool
    {
        return $user->hasPermission('company-update');
    }

    public function delete(User $user): bool
    {
        return $user->hasPermission('company-delete');
    }

    public function restore(User $user): bool
    {
        return $user->hasPermission('company-restore');
    }

    public function authorizeCreate(User $user): bool
    {
        return $user->hasPermission('company-authorizeCreate');
    }

    public function authorizeUpdate(User $user): bool
    {
        return $user->hasPermission('company-authorizeUpdate');
    }

    public function authorizeDelete(User $user): bool
    {
        return $user->hasPermission('company-authorizeDelete');
    }

    public function authorizeRestore(User $user): bool
    {
        return $user->hasPermission('company-authorizeRestore');
    }
}
