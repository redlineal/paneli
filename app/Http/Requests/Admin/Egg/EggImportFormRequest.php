<?php
/**
 * AMGHOST - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Http\Requests\Admin\Egg;

use Amghost\Http\Requests\Admin\AdminFormRequest;

class EggImportFormRequest extends AdminFormRequest
{
    /**
     * @return array
     */
    public function rules()
    {
        $rules = [
            'import_file' => 'bail|required|file|max:1000|mimetypes:application/json,text/plain',
        ];

        if ($this->method() !== 'PUT') {
            $rules['import_to_nest'] = 'bail|required|integer|exists:nests,id';
        }

        return $rules;
    }
}
