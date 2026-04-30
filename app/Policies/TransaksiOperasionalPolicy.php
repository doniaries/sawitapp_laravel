<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\TransaksiOperasional;
use App\Models\TutupHari;
use Illuminate\Auth\Access\HandlesAuthorization;

class TransaksiOperasionalPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:TransaksiOperasional');
    }

    public function view(AuthUser $authUser, TransaksiOperasional $operasional): bool
    {
        return $authUser->can('View:TransaksiOperasional');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:TransaksiOperasional');
    }

    public function update(AuthUser $authUser, TransaksiOperasional $operasional): bool
    {
        if (!$authUser->can('Update:TransaksiOperasional')) {
            return false;
        }

        return TutupHari::canModify($operasional->tanggal, $operasional->perusahaan_id, $authUser);
    }

    public function delete(AuthUser $authUser, TransaksiOperasional $operasional): bool
    {
        if (!$authUser->can('Delete:TransaksiOperasional')) {
            return false;
        }

        return TutupHari::canModify($operasional->tanggal, $operasional->perusahaan_id, $authUser);
    }

    public function restore(AuthUser $authUser, TransaksiOperasional $operasional): bool
    {
        if (!$authUser->can('Restore:TransaksiOperasional')) {
            return false;
        }

        return TutupHari::canModify($operasional->tanggal, $operasional->perusahaan_id, $authUser);
    }

    public function forceDelete(AuthUser $authUser, TransaksiOperasional $operasional): bool
    {
        if (!$authUser->can('ForceDelete:TransaksiOperasional')) {
            return false;
        }

        return TutupHari::canModify($operasional->tanggal, $operasional->perusahaan_id, $authUser);
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:TransaksiOperasional');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:TransaksiOperasional');
    }

    public function replicate(AuthUser $authUser, TransaksiOperasional $operasional): bool
    {
        return $authUser->can('Replicate:TransaksiOperasional');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:TransaksiOperasional');
    }

}