<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\LaporanKeuangan;
use Illuminate\Auth\Access\HandlesAuthorization;

class LaporanKeuanganPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LaporanKeuangan');
    }

    public function view(AuthUser $authUser, LaporanKeuangan $laporanKeuangan): bool
    {
        return $authUser->can('View:LaporanKeuangan');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LaporanKeuangan');
    }

    public function update(AuthUser $authUser, LaporanKeuangan $laporanKeuangan): bool
    {
        return $authUser->can('Update:LaporanKeuangan');
    }

    public function delete(AuthUser $authUser, LaporanKeuangan $laporanKeuangan): bool
    {
        return $authUser->can('Delete:LaporanKeuangan');
    }

    public function restore(AuthUser $authUser, LaporanKeuangan $laporanKeuangan): bool
    {
        return $authUser->can('Restore:LaporanKeuangan');
    }

    public function forceDelete(AuthUser $authUser, LaporanKeuangan $laporanKeuangan): bool
    {
        return $authUser->can('ForceDelete:LaporanKeuangan');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LaporanKeuangan');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LaporanKeuangan');
    }

    public function replicate(AuthUser $authUser, LaporanKeuangan $laporanKeuangan): bool
    {
        return $authUser->can('Replicate:LaporanKeuangan');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LaporanKeuangan');
    }

}