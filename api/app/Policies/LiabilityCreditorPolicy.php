<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\LiabilityCreditor;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LiabilityCreditorPolicy
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

        if ($user->hasPermission('liability_creditor-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?LiabilityCreditor $liability_creditor = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('liability_creditor-read')) {
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

        if ($user->hasPermission('liability_creditor-create')) {
            return true;
        }
    }

    public function update(User $user, ?LiabilityCreditor $liability_creditor = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('liability_creditor-update')) {
            return true;
        }
    }

    public function delete(User $user, ?LiabilityCreditor $liability_creditor = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('liability_creditor-delete')) {
            return true;
        }
    }

    public function restore(User $user, LiabilityCreditor $liability_creditor)
    {
        return false;
    }

    public function forceDelete(User $user, LiabilityCreditor $liability_creditor)
    {
        return false;
    }
}
