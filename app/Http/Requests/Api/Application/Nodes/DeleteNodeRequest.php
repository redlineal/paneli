<?php

namespace Amghost\Http\Requests\Api\Application\Nodes;

use Amghost\Models\Node;
use Amghost\Services\Acl\Api\AdminAcl;
use Amghost\Http\Requests\Api\Application\ApplicationApiRequest;

class DeleteNodeRequest extends ApplicationApiRequest
{
    /**
     * @var string
     */
    protected $resource = AdminAcl::RESOURCE_NODES;

    /**
     * @var int
     */
    protected $permission = AdminAcl::WRITE;

    /**
     * Determine if the node being requested for editing exists
     * on the Panel before validating the data.
     *
     * @return bool
     */
    public function resourceExists(): bool
    {
        $node = $this->route()->parameter('node');

        return $node instanceof Node && $node->exists;
    }
}
