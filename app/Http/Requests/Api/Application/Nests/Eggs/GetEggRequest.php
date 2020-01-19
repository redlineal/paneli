<?php

namespace Amghost\Http\Requests\Api\Application\Nests\Eggs;

use Amghost\Models\Egg;
use Amghost\Models\Nest;
use Amghost\Services\Acl\Api\AdminAcl;
use Amghost\Http\Requests\Api\Application\ApplicationApiRequest;

class GetEggRequest extends ApplicationApiRequest
{
    /**
     * @var string
     */
    protected $resource = AdminAcl::RESOURCE_EGGS;

    /**
     * @var int
     */
    protected $permission = AdminAcl::READ;

    /**
     * Determine if the requested egg exists for the selected nest.
     *
     * @return bool
     */
    public function resourceExists(): bool
    {
        return $this->getModel(Nest::class)->id === $this->getModel(Egg::class)->nest_id;
    }
}
