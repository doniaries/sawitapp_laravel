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
        return $authUser->can('ViewAny:Kendaraan');
    }

    public function view(AuthUser $authUser, Kendaraan $kendaraan): bool
    {
        return $authUser->can('View:Kendaraan');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Kendaraan');
    }

    public function update(AuthUser $authUser, Kendaraan $kendaraan): bool
    {
        return $authUser->can('Update:Kendaraan');
    }

    public function delete(AuthUser $authUser, Kendaraan $kendaraan): bool
    {
        return $authUser->can('Delete:Kendaraan');
    }

    public function restore(AuthUser $authUser, Kendaraan $kendaraan): bool
    {
        return $authUser->can('Restore:Kendaraan');
    }

    public function forceDelete(AuthUser $authUser, Kendaraan $kendaraan): bool
    {
        return $authUser->can('ForceDelete:Kendaraan');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Kendaraan');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Kendaraan');
    }

    public function replicate(AuthUser $authUser, Kendaraan $kendaraan): bool
    {
        return $authUser->can('Replicate:Kendaraan');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Kendaraan');
    }

}