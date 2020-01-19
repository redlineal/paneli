<?php

namespace Amghost\Http\Requests\Api\Application\Users;

use Amghost\Services\Acl\Api\AdminAcl as Acl;
use Amghost\Http\Requests\Api\Application\ApplicationApiRequest;

class GetUsersRequest extends ApplicationApiRequest
{
    /**
     * @var string
     */
    protected $resource = Acl::RESOURCE_USERS;

    /**
     * @var int
     */
    protected $permission = Acl::READ;
}
