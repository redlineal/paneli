<?php
/**
 * AMGHOST - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Http\Requests\Base;

use Amghost\Models\User;
use Amghost\Http\Requests\FrontendUserFormRequest;
use Amghost\Exceptions\Http\Base\InvalidPasswordProvidedException;

class AccountDataFormRequest extends FrontendUserFormRequest
{
    /**
     * @return bool
     * @throws \Amghost\Exceptions\Http\Base\InvalidPasswordProvidedException
     */
    public function authorize()
    {
        if (! parent::authorize()) {
            return false;
        }

        // Verify password matches when changing password or email.
        if (in_array($this->input('do_action'), ['password', 'email'])) {
            if (! password_verify($this->input('current_password'), $this->user()->password)) {
                throw new InvalidPasswordProvidedException(trans('base.account.invalid_password'));
            }
        }

        return true;
    }

    /**
     * @return array
     */
    public function rules()
    {
        $modelRules = User::getUpdateRulesForId($this->user()->id);

        switch ($this->input('do_action')) {
            case 'email':
                $rules = [
                    'new_email' => array_get($modelRules, 'email'),
                ];
                break;
            case 'password':
                $rules = [
                    'new_password' => 'required|confirmed|string|min:8',
                    'new_password_confirmation' => 'required',
                ];
                break;
            case 'identity':
                $rules = [
                    'name_first' => array_get($modelRules, 'name_first'),
                    'name_last' => array_get($modelRules, 'name_last'),
                    'username' => array_get($modelRules, 'username'),
                    'language' => array_get($modelRules, 'language'),
                ];
                break;
            default:
                abort(422);
        }

        return $rules;
    }
}
