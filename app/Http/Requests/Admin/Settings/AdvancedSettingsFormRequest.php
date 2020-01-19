<?php

namespace Amghost\Http\Requests\Admin\Settings;

use Amghost\Http\Requests\Admin\AdminFormRequest;

class AdvancedSettingsFormRequest extends AdminFormRequest
{
    /**
     * Return all of the rules to apply to this request's data.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'recaptcha:enabled' => 'required|in:true,false',
            'recaptcha:secret_key' => 'required|string|max:255',
            'recaptcha:website_key' => 'required|string|max:255',
            'amghost:guzzle:timeout' => 'required|integer|between:1,60',
            'amghost:guzzle:connect_timeout' => 'required|integer|between:1,60',
            'amghost:console:count' => 'required|integer|min:1',
            'amghost:console:frequency' => 'required|integer|min:10',
        ];
    }

    /**
     * @return array
     */
    public function attributes()
    {
        return [
            'recaptcha:enabled' => 'reCAPTCHA Enabled',
            'recaptcha:secret_key' => 'reCAPTCHA Secret Key',
            'recaptcha:website_key' => 'reCAPTCHA Website Key',
            'amghost:guzzle:timeout' => 'HTTP Request Timeout',
            'amghost:guzzle:connect_timeout' => 'HTTP Connection Timeout',
            'amghost:console:count' => 'Console Message Count',
            'amghost:console:frequency' => 'Console Frequency Tick',
        ];
    }
}
