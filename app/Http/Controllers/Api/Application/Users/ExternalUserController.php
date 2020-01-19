<?php

namespace Amghost\Http\Controllers\Api\Application\Users;

use Amghost\Transformers\Api\Application\UserTransformer;
use Amghost\Http\Controllers\Api\Application\ApplicationApiController;
use Amghost\Http\Requests\Api\Application\Users\GetExternalUserRequest;

class ExternalUserController extends ApplicationApiController
{
    /**
     * Retrieve a specific user from the database using their external ID.
     *
     * @param \Amghost\Http\Requests\Api\Application\Users\GetExternalUserRequest $request
     * @return array
     */
    public function index(GetExternalUserRequest $request): array
    {
        return $this->fractal->item($request->getUserModel())
            ->transformWith($this->getTransformer(UserTransformer::class))
            ->toArray();
    }
}
