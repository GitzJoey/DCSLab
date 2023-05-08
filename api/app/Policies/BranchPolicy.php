<?php

namespace App\Policies;

use App\Enums\UserRoles;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BranchPolicy
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

        if ($user->hasPermission('branch-readAny')) {
            return true;
        }
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\Branch  $branch
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Branch $branch = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRoles::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('branch-read')) {
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

        if ($user->hasPermission('branch-create')) {
            return true;
        }
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\Branch  $branch
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Branch $branch = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRoles::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('branch-update')) {
            return true;
        }
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\Branch  $branch
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Branch $branch = null)
    {
        if ($user->roles->isEmpty()) {
            return false;
        }

        if (! app()->environment('production') && $user->hasRole(UserRoles::DEVELOPER->value)) {
            return true;
        }

        if ($user->hasPermission('branch-delete')) {
            return true;
        }
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Branch $branch)
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Branch $branch)
    {
        return false;
    }
}
