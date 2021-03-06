<?php
/**
 * AMGHOST - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Http\Requests\Admin\Node;

use Amghost\Models\Node;
use Amghost\Http\Requests\Admin\AdminFormRequest;

class NodeFormRequest extends AdminFormRequest
{
    /**
     * Get rules to apply to data in this request.
     */
    public function rules()
    {
        if ($this->method() === 'PATCH') {
            return Node::getUpdateRulesForId($this->route()->parameter('node')->id);
        }

        return Node::getCreateRules();
    }

    /**
     * Run validation after the rules above have been applied.
     *
     * @param \Illuminate\Validation\Validator $validator
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check that the FQDN is a valid IP address.
            if (! filter_var(gethostbyname($this->input('fqdn')), FILTER_VALIDATE_IP)) {
                $validator->errors()->add('fqdn', trans('admin/node.validation.fqdn_not_resolvable'));
            }

            // Check that if using HTTPS the FQDN is not an IP address.
            if (filter_var($this->input('fqdn'), FILTER_VALIDATE_IP) && $this->input('scheme') === 'https') {
                $validator->errors()->add('fqdn', trans('admin/node.validation.fqdn_required_for_ssl'));
            }
        });
    }
}
