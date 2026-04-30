<?php

namespace App\Policies;

use App\Models\TutupHari;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TutupHariPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdminOrSuperAdmin() || $user->hasRole(['pimpinan', 'admin', 'kasir']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TutupHari $tutupHari): bool
    {
        return $user->isAdminOrSuperAdmin() || $user->hasRole(['pimpinan', 'admin', 'kasir']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Kasir, Admin, Super Admin can close the day
        return $user->isAdminOrSuperAdmin() || $user->hasRole(['pimpinan', 'admin', 'kasir']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TutupHari $tutupHari): bool
    {
        // Only Admin or Super Admin can edit a closed day record
        return $user->isAdminOrSuperAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TutupHari $tutupHari): bool
    {
        // Only Admin or Super Admin can delete a closed day record (re-opening it)
        return $user->isAdminOrSuperAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TutupHari $tutupHari): bool
    {
        return $user->isAdminOrSuperAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TutupHari $tutupHari): bool
    {
        return $user->isSuperAdmin();
    }
}
