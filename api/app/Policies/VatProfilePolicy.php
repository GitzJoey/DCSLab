<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\User;
use App\Models\VatProfile;
use Illuminate\Auth\Access\HandlesAuthorization;

class VatProfilePolicy
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

        if ($user->hasPermission('vat_profile-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?VatProfile $vatProfile = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('vat_profile-read')) {
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

        if ($user->hasPermission('vat_profile-create')) {
            return true;
        }
    }

    public function update(User $user, ?VatProfile $vatProfile = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('vat_profile-update')) {
            return true;
        }
    }

    public function delete(User $user, ?VatProfile $vatProfile = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('vat_profile-delete')) {
            return true;
        }
    }

    public function restore(User $user, VatProfile $vatProfile)
    {
        return false;
    }

    public function forceDelete(User $user, VatProfile $vatProfile)
    {
        return false;
    }
}
