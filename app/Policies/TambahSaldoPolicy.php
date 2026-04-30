<?php

namespace App\Policies;

use App\Models\User;
use App\Models\TambahSaldo;
use App\Models\TutupHari;
use Illuminate\Auth\Access\HandlesAuthorization;

class TambahSaldoPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('ViewAny:TambahSaldo');
    }

    public function view(User $user, TambahSaldo $tambahSaldo): bool
    {
        return $user->can('View:TambahSaldo');
    }

    public function create(User $user): bool
    {
        return $user->can('Create:TambahSaldo');
    }

    public function update(User $user, TambahSaldo $tambahSaldo): bool
    {
        if (!$user->can('Update:TambahSaldo')) {
            return false;
        }

        return TutupHari::canModify($tambahSaldo->tanggal, $tambahSaldo->perusahaan_id, $user);
    }

    public function delete(User $user, TambahSaldo $tambahSaldo): bool
    {
        if (!$user->can('Delete:TambahSaldo')) {
            return false;
        }

        return TutupHari::canModify($tambahSaldo->tanggal, $tambahSaldo->perusahaan_id, $user);
    }

    public function restore(User $user, TambahSaldo $tambahSaldo): bool
    {
        if (!$user->can('Restore:TambahSaldo')) {
            return false;
        }

        return TutupHari::canModify($tambahSaldo->tanggal, $tambahSaldo->perusahaan_id, $user);
    }

    public function forceDelete(User $user, TambahSaldo $tambahSaldo): bool
    {
        if (!$user->can('ForceDelete:TambahSaldo')) {
            return false;
        }

        return TutupHari::canModify($tambahSaldo->tanggal, $tambahSaldo->perusahaan_id, $user);
    }
}
