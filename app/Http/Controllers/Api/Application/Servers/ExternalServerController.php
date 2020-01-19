<?php

namespace Amghost\Http\Controllers\Api\Application\Servers;

use Amghost\Transformers\Api\Application\ServerTransformer;
use Amghost\Http\Controllers\Api\Application\ApplicationApiController;
use Amghost\Http\Requests\Api\Application\Servers\GetExternalServerRequest;

class ExternalServerController extends ApplicationApiController
{
    /**
     * Retrieve a specific server from the database using its external ID.
     *
     * @param \Amghost\Http\Requests\Api\Application\Servers\GetExternalServerRequest $request
     * @return array
     */
    public function index(GetExternalServerRequest $request): array
    {
        return $this->fractal->item($request->getServerModel())
            ->transformWith($this->getTransformer(ServerTransformer::class))
            ->toArray();
    }
}
