<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Penjual;
use Illuminate\Auth\Access\HandlesAuthorization;

class PenjualPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Penjual');
    }

    public function view(AuthUser $authUser, Penjual $penjual): bool
    {
        return $authUser->can('View:Penjual');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Penjual');
    }

    public function update(AuthUser $authUser, Penjual $penjual): bool
    {
        return $authUser->can('Update:Penjual');
    }

    public function delete(AuthUser $authUser, Penjual $penjual): bool
    {
        return $authUser->can('Delete:Penjual');
    }

    public function restore(AuthUser $authUser, Penjual $penjual): bool
    {
        return $authUser->can('Restore:Penjual');
    }

    public function forceDelete(AuthUser $authUser, Penjual $penjual): bool
    {
        return $authUser->can('ForceDelete:Penjual');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Penjual');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Penjual');
    }

    public function replicate(AuthUser $authUser, Penjual $penjual): bool
    {
        return $authUser->can('Replicate:Penjual');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Penjual');
    }

}