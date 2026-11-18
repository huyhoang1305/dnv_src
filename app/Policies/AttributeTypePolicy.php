<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\AttributeType;
use Illuminate\Auth\Access\HandlesAuthorization;

class AttributeTypePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AttributeType');
    }

    public function view(AuthUser $authUser, AttributeType $attributeType): bool
    {
        return $authUser->can('View:AttributeType');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:AttributeType');
    }

    public function update(AuthUser $authUser, AttributeType $attributeType): bool
    {
        return $authUser->can('Update:AttributeType');
    }

    public function delete(AuthUser $authUser, AttributeType $attributeType): bool
    {
        return $authUser->can('Delete:AttributeType');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:AttributeType');
    }

    public function restore(AuthUser $authUser, AttributeType $attributeType): bool
    {
        return $authUser->can('Restore:AttributeType');
    }

    public function forceDelete(AuthUser $authUser, AttributeType $attributeType): bool
    {
        return $authUser->can('ForceDelete:AttributeType');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:AttributeType');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:AttributeType');
    }

    public function replicate(AuthUser $authUser, AttributeType $attributeType): bool
    {
        return $authUser->can('Replicate:AttributeType');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:AttributeType');
    }

}