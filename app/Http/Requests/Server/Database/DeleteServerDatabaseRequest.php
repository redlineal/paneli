<?php

namespace Amghost\Http\Requests\Server\Database;

use Amghost\Http\Requests\Server\ServerFormRequest;

class DeleteServerDatabaseRequest extends ServerFormRequest
{
    /**
     * @return bool
     */
    public function authorize()
    {
        if (! parent::authorize()) {
            return false;
        }

        return config('amghost.client_features.databases.enabled');
    }

    /**
     * Return the user permission to validate this request against.
     *
     * @return string
     */
    protected function permission(): string
    {
        return 'delete-database';
    }

    /**
     * Rules to validate this request against.
     *
     * @return array
     */
    public function rules()
    {
        return [];
    }
}
