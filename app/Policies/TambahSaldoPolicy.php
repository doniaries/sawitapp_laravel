<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TambahSaldoPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('ViewAny:TambahSaldo');
    }

    public function view(User $user): bool
    {
        return $user->can('View:TambahSaldo');
    }

    public function create(User $user): bool
    {
        return $user->can('Create:TambahSaldo');
    }

    public function update(User $user): bool
    {
        return $user->can('Update:TambahSaldo');
    }

    public function delete(User $user): bool
    {
        return $user->can('Delete:TambahSaldo');
    }

    public function restore(User $user): bool
    {
        return $user->can('Restore:TambahSaldo');
    }

    public function forceDelete(User $user): bool
    {
        return $user->can('ForceDelete:TambahSaldo');
    }
}
