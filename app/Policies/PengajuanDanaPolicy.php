<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PengajuanDanaPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('ViewAny:PengajuanDana');
    }

    public function view(User $user): bool
    {
        return $user->can('View:PengajuanDana');
    }

    public function create(User $user): bool
    {
        return $user->can('Create:PengajuanDana');
    }

    public function update(User $user): bool
    {
        return $user->can('Update:PengajuanDana');
    }

    public function delete(User $user): bool
    {
        return $user->can('Delete:PengajuanDana');
    }

    public function restore(User $user): bool
    {
        return $user->can('Restore:PengajuanDana');
    }

    public function forceDelete(User $user): bool
    {
        return $user->can('ForceDelete:PengajuanDana');
    }
}
