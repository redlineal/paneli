<?php

namespace Amghost\Http\Requests\Api\Application\Nodes;

use Amghost\Services\Acl\Api\AdminAcl;
use Amghost\Http\Requests\Api\Application\ApplicationApiRequest;

class GetNodesRequest extends ApplicationApiRequest
{
    /**
     * @var string
     */
    protected $resource = AdminAcl::RESOURCE_NODES;

    /**
     * @var int
     */
    protected $permission = AdminAcl::READ;
}
