<?php

namespace Amghost\Http\Requests\Api\Application\Users;

use Amghost\Models\User;
use Amghost\Services\Acl\Api\AdminAcl;
use Amghost\Http\Requests\Api\Application\ApplicationApiRequest;

class DeleteUserRequest extends ApplicationApiRequest
{
    /**
     * @var string
     */
    protected $resource = AdminAcl::RESOURCE_USERS;

    /**
     * @var int
     */
    protected $permission = AdminAcl::WRITE;

    /**
     * Determine if the requested user exists on the Panel.
     *
     * @return bool
     */
    public function resourceExists(): bool
    {
        $user = $this->route()->parameter('user');

        return $user instanceof User && $user->exists;
    }
}
