<?php

namespace Amghost\Http\Requests\Api\Application\Users;

use Amghost\Models\User;

class UpdateUserRequest extends StoreUserRequest
{
    /**
     * Return the validation rules for this request.
     *
     * @param array|null $rules
     * @return array
     */
    public function rules(array $rules = null): array
    {
        $userId = $this->getModel(User::class)->id;

        return parent::rules(User::getUpdateRulesForId($userId));
    }
}
