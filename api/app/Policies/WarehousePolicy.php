<?php

namespace App\Policies;

use App\Enums\UserRoles;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Auth\Access\HandlesAuthorization;

class WarehousePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRoles::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('warehouse-readAny')) {
            return true;
        }
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\Warehouse  $warehouse
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Warehouse $warehouse = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRoles::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('warehouse-read')) {
            return true;
        }
    }

    /**
     * Determine whether the user can create models.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRoles::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('warehouse-create')) {
            return true;
        }
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\Warehouse  $warehouse
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Warehouse $warehouse = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRoles::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('warehouse-update')) {
            return true;
        }
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\Warehouse  $warehouse
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Warehouse $warehouse = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRoles::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('warehouse-delete')) {
            return true;
        }
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Warehouse $warehouse)
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Warehouse $warehouse)
    {
        return false;
    }
}
