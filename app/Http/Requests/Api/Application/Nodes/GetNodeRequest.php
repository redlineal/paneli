<?php

namespace Amghost\Http\Requests\Api\Application\Nodes;

use Amghost\Models\Node;

class GetNodeRequest extends GetNodesRequest
{
    /**
     * Determine if the requested node exists on the Panel.
     *
     * @return bool
     */
    public function resourceExists(): bool
    {
        $node = $this->route()->parameter('node');

        return $node instanceof Node && $node->exists;
    }
}
