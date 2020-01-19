<?php

namespace Amghost\Http\Requests\Base;

use Amghost\Http\Requests\FrontendUserFormRequest;

class StoreAccountKeyRequest extends FrontendUserFormRequest
{
    /**
     * Rules to validate the request input against before storing
     * an account API key.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'memo' => 'required|nullable|string|max:500',
            'allowed_ips' => 'present',
            'allowed_ips.*' => 'sometimes|string',
        ];
    }
}
