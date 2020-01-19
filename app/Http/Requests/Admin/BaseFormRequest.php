<?php
/**
 * AMGHOST - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Http\Requests\Admin;

class BaseFormRequest extends AdminFormRequest
{
    public function rules()
    {
        return [
            'company' => 'required|between:1,256',
        ];
    }
}
