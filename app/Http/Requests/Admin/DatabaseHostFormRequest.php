<?php

namespace Amghost\Http\Requests\Admin;

use Amghost\Models\DatabaseHost;

class DatabaseHostFormRequest extends AdminFormRequest
{
    /**
     * @return mixed
     */
    public function rules()
    {
        if ($this->method() !== 'POST') {
            return DatabaseHost::getUpdateRulesForId($this->route()->parameter('host'));
        }

        return DatabaseHost::getCreateRules();
    }

    /**
     * Modify submitted data before it is passed off to the validator.
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function getValidatorInstance()
    {
        if (! $this->filled('node_id')) {
            $this->merge(['node_id' => null]);
        }

        $this->merge([
            'host' => gethostbyname($this->input('host')),
        ]);

        return parent::getValidatorInstance();
    }
}
