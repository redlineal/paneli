<?php

namespace Amghost\Http\Requests\Api\Application\Nests;

use Amghost\Services\Acl\Api\AdminAcl;
use Amghost\Http\Requests\Api\Application\ApplicationApiRequest;

class GetNestsRequest extends ApplicationApiRequest
{
    /**
     * @var string
     */
    protected $resource = AdminAcl::RESOURCE_NESTS;

    /**
     * @var int
     */
    protected $permission = AdminAcl::READ;
}
