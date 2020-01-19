<?php

namespace Amghost\Http\Requests\Server\Subuser;

use Amghost\Http\Requests\Server\ServerFormRequest;

class SubuserUpdateFormRequest extends ServerFormRequest
{
    /**
     * Return the user permission to validate this request against.
     *
     * @return string
     */
    protected function permission(): string
    {
        return 'edit-subuser';
    }

    /**
     * The rules to validate this request submission against.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'permissions' => 'present|array',
        ];
    }
}
