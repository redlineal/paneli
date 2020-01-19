<?php

namespace Amghost\Repositories\Eloquent;

use Amghost\Models\Permission;
use Amghost\Contracts\Repository\PermissionRepositoryInterface;

class PermissionRepository extends EloquentRepository implements PermissionRepositoryInterface
{
    /**
     * Return the model backing this repository.
     *
     * @return string
     */
    public function model()
    {
        return Permission::class;
    }
}
