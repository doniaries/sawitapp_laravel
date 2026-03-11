<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Pekerja;
use Illuminate\Auth\Access\HandlesAuthorization;

class PekerjaPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Pekerja');
    }

    public function view(AuthUser $authUser, Pekerja $pekerja): bool
    {
        return $authUser->can('View:Pekerja');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Pekerja');
    }

    public function update(AuthUser $authUser, Pekerja $pekerja): bool
    {
        return $authUser->can('Update:Pekerja');
    }

    public function delete(AuthUser $authUser, Pekerja $pekerja): bool
    {
        return $authUser->can('Delete:Pekerja');
    }

    public function restore(AuthUser $authUser, Pekerja $pekerja): bool
    {
        return $authUser->can('Restore:Pekerja');
    }

    public function forceDelete(AuthUser $authUser, Pekerja $pekerja): bool
    {
        return $authUser->can('ForceDelete:Pekerja');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Pekerja');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Pekerja');
    }

    public function replicate(AuthUser $authUser, Pekerja $pekerja): bool
    {
        return $authUser->can('Replicate:Pekerja');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Pekerja');
    }

}