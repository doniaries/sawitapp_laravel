<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\TransaksiOperasional;
use Illuminate\Auth\Access\HandlesAuthorization;

class TransaksiOperasionalPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:TransaksiOperasional');
    }

    public function view(AuthUser $authUser, TransaksiOperasional $operasional): bool
    {
        return $authUser->can('View:TransaksiOperasional');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:TransaksiOperasional');
    }

    public function update(AuthUser $authUser, TransaksiOperasional $operasional): bool
    {
        return $authUser->can('Update:TransaksiOperasional');
    }

    public function delete(AuthUser $authUser, TransaksiOperasional $operasional): bool
    {
        return $authUser->can('Delete:TransaksiOperasional');
    }

    public function restore(AuthUser $authUser, TransaksiOperasional $operasional): bool
    {
        return $authUser->can('Restore:TransaksiOperasional');
    }

    public function forceDelete(AuthUser $authUser, TransaksiOperasional $operasional): bool
    {
        return $authUser->can('ForceDelete:TransaksiOperasional');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:TransaksiOperasional');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:TransaksiOperasional');
    }

    public function replicate(AuthUser $authUser, TransaksiOperasional $operasional): bool
    {
        return $authUser->can('Replicate:TransaksiOperasional');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:TransaksiOperasional');
    }

}