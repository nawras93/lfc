<?php

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ParentAccountPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ParentAccount');
    }

    public function view(AuthUser $authUser): bool
    {
        return $authUser->can('View:ParentAccount');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ParentAccount');
    }

    public function update(AuthUser $authUser): bool
    {
        return $authUser->can('Update:ParentAccount');
    }

    public function delete(AuthUser $authUser): bool
    {
        return $authUser->can('Delete:ParentAccount');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ParentAccount');
    }

    public function restore(AuthUser $authUser): bool
    {
        return $authUser->can('Restore:ParentAccount');
    }

    public function forceDelete(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDelete:ParentAccount');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ParentAccount');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ParentAccount');
    }

    public function replicate(AuthUser $authUser): bool
    {
        return $authUser->can('Replicate:ParentAccount');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ParentAccount');
    }
}
