<?php

namespace App\Policies;

use App\Models\Unit;
use App\Models\User;
use App\Enums\UserRoles;
use Illuminate\Auth\Access\HandlesAuthorization;

class UnitPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        if ($user->roles->isEmpty()) return false;

        if ($user->hasRole(UserRoles::DEVELOPER->value)) return true;

        if ($user->hasPermission('unit-read')) return true;
    }

    public function view(User $user, Unit $unit = null)
    {
        return $this->viewAny($user);
    }

    public function create(User $user)
    {
        if ($user->roles->isEmpty()) return false;

        if ($user->hasRole(UserRoles::DEVELOPER->value)) return true;

        if ($user->hasPermission('unit-create')) return true;
    }

    public function update(User $user, Unit $unit = null)
    {
        if ($user->roles->isEmpty()) return false;

        if ($user->hasRole(UserRoles::DEVELOPER->value)) return true;
    
        if ($user->hasPermission('unit-update')) return true;
    }

    public function delete(User $user, Unit $unit = null)
    {
        if ($user->roles->isEmpty()) return false;

        if ($user->hasRole(UserRoles::DEVELOPER->value)) return true;
    
        if ($user->hasPermission('unit-delete')) return true;
    }

    public function restore(User $user, Unit $unit)
    {
        return false;
    }

    public function forceDelete(User $user, Unit $unit)
    {
        return false;
    }
}
