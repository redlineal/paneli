<?php

namespace Amghost\Http\Requests\Api\Client;

class GetServersRequest extends ClientApiRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }
}
