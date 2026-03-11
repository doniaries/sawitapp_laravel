<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Perusahaan;
use Illuminate\Auth\Access\HandlesAuthorization;

class PerusahaanPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return true;
        // return $authUser->can('view_any_perusahaan');
    }

    public function view(AuthUser $authUser, Perusahaan $perusahaan): bool
    {
        return $authUser->can('view_perusahaan');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_perusahaan');
    }

    public function update(AuthUser $authUser, Perusahaan $perusahaan): bool
    {
        return $authUser->can('update_perusahaan');
    }

    public function delete(AuthUser $authUser, Perusahaan $perusahaan): bool
    {
        return $authUser->can('delete_perusahaan');
    }

    public function restore(AuthUser $authUser, Perusahaan $perusahaan): bool
    {
        return $authUser->can('restore_perusahaan');
    }

    public function forceDelete(AuthUser $authUser, Perusahaan $perusahaan): bool
    {
        return $authUser->can('force_delete_perusahaan');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_perusahaan');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_perusahaan');
    }

    public function replicate(AuthUser $authUser, Perusahaan $perusahaan): bool
    {
        return $authUser->can('replicate_perusahaan');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder_perusahaan');
    }

}