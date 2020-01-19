<?php

namespace Amghost\Http\Requests\Api\Application\Allocations;

use Amghost\Models\Node;
use Amghost\Services\Acl\Api\AdminAcl;
use Amghost\Http\Requests\Api\Application\ApplicationApiRequest;

class GetAllocationsRequest extends ApplicationApiRequest
{
    /**
     * @var string
     */
    protected $resource = AdminAcl::RESOURCE_ALLOCATIONS;

    /**
     * @var int
     */
    protected $permission = AdminAcl::READ;

    /**
     * Determine if the node that we are requesting the allocations
     * for exists on the Panel.
     *
     * @return bool
     */
    public function resourceExists(): bool
    {
        $node = $this->route()->parameter('node');

        return $node instanceof Node && $node->exists;
    }
}
