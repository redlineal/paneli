<?php
/**
 * AMGHOST - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Http\Requests\Admin;

use Amghost\Models\Location;

class LocationFormRequest extends AdminFormRequest
{
    /**
     * Setup the validation rules to use for these requests.
     *
     * @return array
     */
    public function rules()
    {
        if ($this->method() === 'PATCH') {
            return Location::getUpdateRulesForId($this->route()->parameter('location')->id);
        }

        return Location::getCreateRules();
    }
}
