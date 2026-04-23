<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\LiabilityPayment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LiabilityPaymentPolicy
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

        if ($user->hasPermission('liability_payment-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?LiabilityPayment $liabilityPayment = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('liability_payment-read')) {
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

        if ($user->hasPermission('liability_payment-create')) {
            return true;
        }
    }

    public function update(User $user, ?LiabilityPayment $liabilityPayment = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('liability_payment-update')) {
            return true;
        }
    }

    public function delete(User $user, ?LiabilityPayment $liabilityPayment = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('liability_payment-delete')) {
            return true;
        }
    }

    public function restore(User $user, LiabilityPayment $liabilityPayment)
    {
        return false;
    }

    public function forceDelete(User $user, LiabilityPayment $liabilityPayment)
    {
        return false;
    }
}
