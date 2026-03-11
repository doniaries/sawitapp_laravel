<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Supir;
use Illuminate\Auth\Access\HandlesAuthorization;

class SupirPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Supir');
    }

    public function view(AuthUser $authUser, Supir $supir): bool
    {
        return $authUser->can('View:Supir');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Supir');
    }

    public function update(AuthUser $authUser, Supir $supir): bool
    {
        return $authUser->can('Update:Supir');
    }

    public function delete(AuthUser $authUser, Supir $supir): bool
    {
        return $authUser->can('Delete:Supir');
    }

    public function restore(AuthUser $authUser, Supir $supir): bool
    {
        return $authUser->can('Restore:Supir');
    }

    public function forceDelete(AuthUser $authUser, Supir $supir): bool
    {
        return $authUser->can('ForceDelete:Supir');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Supir');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Supir');
    }

    public function replicate(AuthUser $authUser, Supir $supir): bool
    {
        return $authUser->can('Replicate:Supir');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Supir');
    }

}