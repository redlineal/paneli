<?php

namespace Amghost\Http\Controllers\Api\Client\Servers;

use Amghost\Models\Server;
use Amghost\Transformers\Api\Client\ServerTransformer;
use Amghost\Http\Controllers\Api\Client\ClientApiController;
use Amghost\Http\Requests\Api\Client\Servers\GetServerRequest;

class ServerController extends ClientApiController
{
    /**
     * Transform an individual server into a response that can be consumed by a
     * client using the API.
     *
     * @param \Amghost\Http\Requests\Api\Client\Servers\GetServerRequest $request
     * @return array
     */
    public function index(GetServerRequest $request): array
    {
        return $this->fractal->item($request->getModel(Server::class))
            ->transformWith($this->getTransformer(ServerTransformer::class))
            ->toArray();
    }
}
