<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ContentType;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContentTypePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ContentType');
    }

    public function view(AuthUser $authUser, ContentType $contentType): bool
    {
        return $authUser->can('View:ContentType');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ContentType');
    }

    public function update(AuthUser $authUser, ContentType $contentType): bool
    {
        return $authUser->can('Update:ContentType');
    }

    public function delete(AuthUser $authUser, ContentType $contentType): bool
    {
        return $authUser->can('Delete:ContentType');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ContentType');
    }

    public function restore(AuthUser $authUser, ContentType $contentType): bool
    {
        return $authUser->can('Restore:ContentType');
    }

    public function forceDelete(AuthUser $authUser, ContentType $contentType): bool
    {
        return $authUser->can('ForceDelete:ContentType');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ContentType');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ContentType');
    }

    public function replicate(AuthUser $authUser, ContentType $contentType): bool
    {
        return $authUser->can('Replicate:ContentType');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ContentType');
    }

}