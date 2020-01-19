<?php
/**
 * AMGHOST - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Http\Requests\Admin\Egg;

use Amghost\Http\Requests\Admin\AdminFormRequest;

class EggScriptFormRequest extends AdminFormRequest
{
    /**
     * Return the rules to be used when validating the sent data in the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'script_install' => 'sometimes|nullable|string',
            'script_is_privileged' => 'sometimes|required|boolean',
            'script_entry' => 'sometimes|required|string',
            'script_container' => 'sometimes|required|string',
            'copy_script_from' => 'sometimes|nullable|numeric',
        ];
    }
}
