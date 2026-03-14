<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Operasional;
use Illuminate\Auth\Access\HandlesAuthorization;

class OperasionalPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Operasional');
    }

    public function view(AuthUser $authUser, Operasional $operasional): bool
    {
        return $authUser->can('View:Operasional');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Operasional');
    }

    public function update(AuthUser $authUser, Operasional $operasional): bool
    {
        return $authUser->can('Update:Operasional');
    }

    public function delete(AuthUser $authUser, Operasional $operasional): bool
    {
        return $authUser->can('Delete:Operasional');
    }

    public function restore(AuthUser $authUser, Operasional $operasional): bool
    {
        return $authUser->can('Restore:Operasional');
    }

    public function forceDelete(AuthUser $authUser, Operasional $operasional): bool
    {
        return $authUser->can('ForceDelete:Operasional');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Operasional');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Operasional');
    }

    public function replicate(AuthUser $authUser, Operasional $operasional): bool
    {
        return $authUser->can('Replicate:Operasional');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Operasional');
    }

}