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
        return true;
        // return $authUser->can('view_any_laporan_keuangan');
    }

    public function view(AuthUser $authUser, LaporanKeuangan $laporanKeuangan): bool
    {
        return $authUser->can('view_laporan_keuangan');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_laporan_keuangan');
    }

    public function update(AuthUser $authUser, LaporanKeuangan $laporanKeuangan): bool
    {
        return $authUser->can('update_laporan_keuangan');
    }

    public function delete(AuthUser $authUser, LaporanKeuangan $laporanKeuangan): bool
    {
        return $authUser->can('delete_laporan_keuangan');
    }

    public function restore(AuthUser $authUser, LaporanKeuangan $laporanKeuangan): bool
    {
        return $authUser->can('restore_laporan_keuangan');
    }

    public function forceDelete(AuthUser $authUser, LaporanKeuangan $laporanKeuangan): bool
    {
        return $authUser->can('force_delete_laporan_keuangan');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_laporan_keuangan');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_laporan_keuangan');
    }

    public function replicate(AuthUser $authUser, LaporanKeuangan $laporanKeuangan): bool
    {
        return $authUser->can('replicate_laporan_keuangan');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder_laporan_keuangan');
    }

}