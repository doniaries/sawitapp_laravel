<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Operasional;
use Illuminate\Auth\Access\HandlesAuthorization;

class OperasionalPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return true;
        // return $authUser->can('view_any_operasional');
    }

    public function view(AuthUser $authUser, Operasional $operasional): bool
    {
        return $authUser->can('view_operasional');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_operasional');
    }

    public function update(AuthUser $authUser, Operasional $operasional): bool
    {
        return $authUser->can('update_operasional');
    }

    public function delete(AuthUser $authUser, Operasional $operasional): bool
    {
        return $authUser->can('delete_operasional');
    }

    public function restore(AuthUser $authUser, Operasional $operasional): bool
    {
        return $authUser->can('restore_operasional');
    }

    public function forceDelete(AuthUser $authUser, Operasional $operasional): bool
    {
        return $authUser->can('force_delete_operasional');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_operasional');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_operasional');
    }

    public function replicate(AuthUser $authUser, Operasional $operasional): bool
    {
        return $authUser->can('replicate_operasional');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder_operasional');
    }

}