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

    public function view (User $user): bool
    {
        return $user->hasPermission('profile-read');
    }

    public function update (User $user): bool
    {
        return $user->hasPermission('profile-update');
    }
}
