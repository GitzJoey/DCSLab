<?php

namespace App\Policies;

use App\Enums\UserRolesEnum;
use App\Models\CashTransfer;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CashTransferPolicy
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

        if ($user->hasPermission('cash_transfer-readAny')) {
            return true;
        }
    }

    public function view(User $user, ?CashTransfer $cashTransfer = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('cash_transfer-read')) {
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

        if ($user->hasPermission('cash_transfer-create')) {
            return true;
        }
    }

    public function update(User $user, ?CashTransfer $cashTransfer = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('cash_transfer-update')) {
            return true;
        }
    }

    public function delete(User $user, ?CashTransfer $cashTransfer = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRolesEnum::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('cash_transfer-delete')) {
            return true;
        }
    }

    public function restore(User $user, CashTransfer $cashTransfer)
    {
        return false;
    }

    public function forceDelete(User $user, CashTransfer $cashTransfer)
    {
        return false;
    }
}
