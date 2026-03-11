<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Kendaraan;
use Illuminate\Auth\Access\HandlesAuthorization;

class KendaraanPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return true;
        // return $authUser->can('view_any_kendaraan');
    }

    public function view(AuthUser $authUser, Kendaraan $kendaraan): bool
    {
        return $authUser->can('view_kendaraan');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_kendaraan');
    }

    public function update(AuthUser $authUser, Kendaraan $kendaraan): bool
    {
        return $authUser->can('update_kendaraan');
    }

    public function delete(AuthUser $authUser, Kendaraan $kendaraan): bool
    {
        return $authUser->can('delete_kendaraan');
    }

    public function restore(AuthUser $authUser, Kendaraan $kendaraan): bool
    {
        return $authUser->can('restore_kendaraan');
    }

    public function forceDelete(AuthUser $authUser, Kendaraan $kendaraan): bool
    {
        return $authUser->can('force_delete_kendaraan');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_kendaraan');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_kendaraan');
    }

    public function replicate(AuthUser $authUser, Kendaraan $kendaraan): bool
    {
        return $authUser->can('replicate_kendaraan');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder_kendaraan');
    }

}