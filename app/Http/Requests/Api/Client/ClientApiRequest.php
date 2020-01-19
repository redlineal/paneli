<?php

namespace Amghost\Http\Requests\Api\Client;

use Amghost\Http\Requests\Api\Application\ApplicationApiRequest;

abstract class ClientApiRequest extends ApplicationApiRequest
{
    /**
     * Determine if the current user is authorized to perform
     * the requested action against the API.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }
}
