<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\JurnalKeuangan;
use App\Models\TutupHari;
use Illuminate\Auth\Access\HandlesAuthorization;

class JurnalKeuanganPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:JurnalKeuangan');
    }

    public function view(AuthUser $authUser, JurnalKeuangan $jurnalKeuangan): bool
    {
        return $authUser->can('View:JurnalKeuangan');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:JurnalKeuangan');
    }

    public function update(AuthUser $authUser, JurnalKeuangan $jurnalKeuangan): bool
    {
        if (!$authUser->can('Update:JurnalKeuangan')) {
            return false;
        }

        return TutupHari::canModify($jurnalKeuangan->tanggal, $jurnalKeuangan->perusahaan_id, $authUser);
    }

    public function delete(AuthUser $authUser, JurnalKeuangan $jurnalKeuangan): bool
    {
        if (!$authUser->can('Delete:JurnalKeuangan')) {
            return false;
        }

        return TutupHari::canModify($jurnalKeuangan->tanggal, $jurnalKeuangan->perusahaan_id, $authUser);
    }

    public function restore(AuthUser $authUser, JurnalKeuangan $jurnalKeuangan): bool
    {
        if (!$authUser->can('Restore:JurnalKeuangan')) {
            return false;
        }

        return TutupHari::canModify($jurnalKeuangan->tanggal, $jurnalKeuangan->perusahaan_id, $authUser);
    }

    public function forceDelete(AuthUser $authUser, JurnalKeuangan $jurnalKeuangan): bool
    {
        if (!$authUser->can('ForceDelete:JurnalKeuangan')) {
            return false;
        }

        return TutupHari::canModify($jurnalKeuangan->tanggal, $jurnalKeuangan->perusahaan_id, $authUser);
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:JurnalKeuangan');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:JurnalKeuangan');
    }

    public function replicate(AuthUser $authUser, JurnalKeuangan $jurnalKeuangan): bool
    {
        return $authUser->can('Replicate:JurnalKeuangan');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:JurnalKeuangan');
    }

}