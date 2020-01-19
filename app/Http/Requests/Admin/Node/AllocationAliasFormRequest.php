<?php
/**
 * AMGHOST - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Http\Requests\Admin\Node;

use Amghost\Http\Requests\Admin\AdminFormRequest;

class AllocationAliasFormRequest extends AdminFormRequest
{
    /**
     * @return array
     */
    public function rules()
    {
        return [
            'alias' => 'present|nullable|string',
            'allocation_id' => 'required|numeric|exists:allocations,id',
        ];
    }
}
