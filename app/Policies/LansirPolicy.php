<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Lansir;
use App\Models\TutupHari;
use Illuminate\Auth\Access\HandlesAuthorization;

class LansirPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Lansir');
    }

    public function view(AuthUser $authUser, Lansir $lansir): bool
    {
        return $authUser->can('View:Lansir');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Lansir');
    }

    public function update(AuthUser $authUser, Lansir $lansir): bool
    {
        if (!$authUser->can('Update:Lansir')) {
            return false;
        }

        return TutupHari::canModify($lansir->tanggal_lansir, $lansir->perusahaan_id, $authUser);
    }

    public function delete(AuthUser $authUser, Lansir $lansir): bool
    {
        if (!$authUser->can('Delete:Lansir')) {
            return false;
        }

        return TutupHari::canModify($lansir->tanggal_lansir, $lansir->perusahaan_id, $authUser);
    }

    public function restore(AuthUser $authUser, Lansir $lansir): bool
    {
        if (!$authUser->can('Restore:Lansir')) {
            return false;
        }

        return TutupHari::canModify($lansir->tanggal_lansir, $lansir->perusahaan_id, $authUser);
    }

    public function forceDelete(AuthUser $authUser, Lansir $lansir): bool
    {
        if (!$authUser->can('ForceDelete:Lansir')) {
            return false;
        }

        return TutupHari::canModify($lansir->tanggal_lansir, $lansir->perusahaan_id, $authUser);
    }
}
