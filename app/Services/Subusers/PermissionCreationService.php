<?php
/**
 * AMGHOST - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Services\Subusers;

use Webmozart\Assert\Assert;
use Amghost\Models\Permission;
use Amghost\Contracts\Repository\PermissionRepositoryInterface;

class PermissionCreationService
{
    /**
     * @var \Amghost\Contracts\Repository\PermissionRepositoryInterface
     */
    protected $repository;

    /**
     * PermissionCreationService constructor.
     *
     * @param \Amghost\Contracts\Repository\PermissionRepositoryInterface $repository
     */
    public function __construct(PermissionRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Assign permissions to a given subuser.
     *
     * @param int   $subuser
     * @param array $permissions
     *
     * @throws \Amghost\Exceptions\Model\DataValidationException
     */
    public function handle($subuser, array $permissions)
    {
        Assert::integerish($subuser, 'First argument passed to handle must be an integer, received %s.');

        $permissionMappings = Permission::getPermissions(true);
        $insertPermissions = [];

        foreach ($permissions as $permission) {
            if (array_key_exists($permission, $permissionMappings)) {
                Assert::stringNotEmpty($permission, 'Permission argument provided must be a non-empty string, received %s.');

                array_push($insertPermissions, [
                    'subuser_id' => $subuser,
                    'permission' => $permission,
                ]);
            }
        }

        if (! empty($insertPermissions)) {
            $this->repository->withoutFreshModel()->insert($insertPermissions);
        }
    }
}
