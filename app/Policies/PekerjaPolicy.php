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
        return true;
        // return $authUser->can('view_any_pekerja');
    }

    public function view(AuthUser $authUser): bool
    {
        return $authUser->can('view_pekerja');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_pekerja');
    }

    public function update(AuthUser $authUser): bool
    {
        return $authUser->can('update_pekerja');
    }

    public function delete(AuthUser $authUser): bool
    {
        return $authUser->can('delete_pekerja');
    }

    public function restore(AuthUser $authUser): bool
    {
        return $authUser->can('restore_pekerja');
    }

    public function forceDelete(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_pekerja');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_pekerja');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_pekerja');
    }

    public function replicate(AuthUser $authUser): bool
    {
        return $authUser->can('replicate_pekerja');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder_pekerja');
    }

}