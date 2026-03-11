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
        return true;
        // return $authUser->can('view_any_penjual');
    }

    public function view(AuthUser $authUser): bool
    {
        return $authUser->can('view_penjual');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_penjual');
    }

    public function update(AuthUser $authUser): bool
    {
        return $authUser->can('update_penjual');
    }

    public function delete(AuthUser $authUser): bool
    {
        return $authUser->can('delete_penjual');
    }

    public function restore(AuthUser $authUser): bool
    {
        return $authUser->can('restore_penjual');
    }

    public function forceDelete(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_penjual');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_penjual');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_penjual');
    }

    public function replicate(AuthUser $authUser): bool
    {
        return $authUser->can('replicate_penjual');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder_penjual');
    }

}