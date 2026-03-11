<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\TransaksiDo;
use Illuminate\Auth\Access\HandlesAuthorization;

class TransaksiDoPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return true;
        // return $authUser->can('view_any_transaksi_do');
    }

    public function view(AuthUser $authUser, TransaksiDo $transaksiDo): bool
    {
        return $authUser->can('view_transaksi_do');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_transaksi_do');
    }

    public function update(AuthUser $authUser, TransaksiDo $transaksiDo): bool
    {
        return $authUser->can('update_transaksi_do');
    }

    public function delete(AuthUser $authUser, TransaksiDo $transaksiDo): bool
    {
        return $authUser->can('delete_transaksi_do');
    }

    public function restore(AuthUser $authUser, TransaksiDo $transaksiDo): bool
    {
        return $authUser->can('restore_transaksi_do');
    }

    public function forceDelete(AuthUser $authUser, TransaksiDo $transaksiDo): bool
    {
        return $authUser->can('force_delete_transaksi_do');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_transaksi_do');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_transaksi_do');
    }

    public function replicate(AuthUser $authUser, TransaksiDo $transaksiDo): bool
    {
        return $authUser->can('replicate_transaksi_do');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder_transaksi_do');
    }

}