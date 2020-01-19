<?php
/**
 * AMGHOST - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Http\Requests\Admin;

use Amghost\Models\Server;
use Illuminate\Validation\Rule;

class ServerFormRequest extends AdminFormRequest
{
    /**
     * Rules to be applied to this request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = Server::getCreateRules();
        $rules['description'][] = 'nullable';

        return $rules;
    }

    /**
     * Run validation after the rules above have been applied.
     *
     * @param \Illuminate\Validation\Validator $validator
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $validator->sometimes('node_id', 'required|numeric|bail|exists:nodes,id', function ($input) {
                return ! ($input->auto_deploy);
            });

            $validator->sometimes('allocation_id', [
                'required',
                'numeric',
                'bail',
                Rule::exists('allocations', 'id')->where(function ($query) {
                    $query->where('node_id', $this->input('node_id'));
                    $query->whereNull('server_id');
                }),
            ], function ($input) {
                return ! ($input->auto_deploy);
            });

            $validator->sometimes('allocation_additional.*', [
                'sometimes',
                'required',
                'numeric',
                Rule::exists('allocations', 'id')->where(function ($query) {
                    $query->where('node_id', $this->input('node_id'));
                    $query->whereNull('server_id');
                }),
            ], function ($input) {
                return ! ($input->auto_deploy);
            });

            $validator->sometimes('pack_id', [
                Rule::exists('packs', 'id')->where(function ($query) {
                    $query->where('selectable', 1);
                    $query->where('egg_id', $this->input('egg_id'));
                }),
            ], function ($input) {
                return $input->pack_id !== 0 && $input->pack_id !== null;
            });
        });
    }
}
