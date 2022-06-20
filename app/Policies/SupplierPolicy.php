<?php

namespace App\Policies;

use App\Enums\UserRoles;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SupplierPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        if ($user->roles->isEmpty()) return false;

        if ($user->hasRole(UserRoles::DEVELOPER->value)) return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Supplier  $supplier
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Supplier $supplier)
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        if ($user->roles->isEmpty()) return false;

        if ($user->hasRole(UserRoles::DEVELOPER->value)) return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Supplier  $supplier
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Supplier $supplier)
    {
        if ($user->roles->isEmpty()) return false;

        if ($user->hasRole(UserRoles::DEVELOPER->value)) return true;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Supplier  $supplier
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Supplier $supplier)
    {
        if ($user->roles->isEmpty()) return false;

        if ($user->hasRole(UserRoles::DEVELOPER->value)) return true;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Supplier  $supplier
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Supplier $supplier)
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Supplier  $supplier
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Supplier $supplier)
    {
        return false;
    }
}
