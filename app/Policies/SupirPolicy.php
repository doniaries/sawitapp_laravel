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
        return true;
        // return $authUser->can('view_any_supir');
    }

    public function view(AuthUser $authUser): bool
    {
        return $authUser->can('view_supir');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_supir');
    }

    public function update(AuthUser $authUser): bool
    {
        return $authUser->can('update_supir');
    }

    public function delete(AuthUser $authUser): bool
    {
        return $authUser->can('delete_supir');
    }

    public function restore(AuthUser $authUser): bool
    {
        return $authUser->can('restore_supir');
    }

    public function forceDelete(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_supir');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_supir');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_supir');
    }

    public function replicate(AuthUser $authUser): bool
    {
        return $authUser->can('replicate_supir');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder_supir');
    }

}