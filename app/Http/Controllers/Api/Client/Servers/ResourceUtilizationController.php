<?php

namespace Amghost\Http\Controllers\Api\Client\Servers;

use Amghost\Models\Server;
use Amghost\Transformers\Api\Client\StatsTransformer;
use Amghost\Http\Controllers\Api\Client\ClientApiController;
use Amghost\Http\Requests\Api\Client\Servers\GetServerRequest;

class ResourceUtilizationController extends ClientApiController
{
    /**
     * Return the current resource utilization for a server.
     *
     * @param \Amghost\Http\Requests\Api\Client\Servers\GetServerRequest $request
     * @return array
     */
    public function index(GetServerRequest $request): array
    {
        return $this->fractal->item($request->getModel(Server::class))
            ->transformWith($this->getTransformer(StatsTransformer::class))
            ->toArray();
    }
}
