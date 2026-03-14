<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\TransaksiDo;
use Illuminate\Auth\Access\HandlesAuthorization;

class TransaksiDoPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:TransaksiDo');
    }

    public function view(AuthUser $authUser, TransaksiDo $transaksiDo): bool
    {
        return $authUser->can('View:TransaksiDo');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:TransaksiDo');
    }

    public function update(AuthUser $authUser, TransaksiDo $transaksiDo): bool
    {
        return $authUser->can('Update:TransaksiDo');
    }

    public function delete(AuthUser $authUser, TransaksiDo $transaksiDo): bool
    {
        return $authUser->can('Delete:TransaksiDo');
    }

    public function restore(AuthUser $authUser, TransaksiDo $transaksiDo): bool
    {
        return $authUser->can('Restore:TransaksiDo');
    }

    public function forceDelete(AuthUser $authUser, TransaksiDo $transaksiDo): bool
    {
        return $authUser->can('ForceDelete:TransaksiDo');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:TransaksiDo');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:TransaksiDo');
    }

    public function replicate(AuthUser $authUser, TransaksiDo $transaksiDo): bool
    {
        return $authUser->can('Replicate:TransaksiDo');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:TransaksiDo');
    }

}