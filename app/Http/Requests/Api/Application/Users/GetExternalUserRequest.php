<?php

namespace Amghost\Http\Requests\Api\Application\Users;

use Amghost\Models\User;
use Amghost\Services\Acl\Api\AdminAcl;
use Amghost\Contracts\Repository\UserRepositoryInterface;
use Amghost\Exceptions\Repository\RecordNotFoundException;
use Amghost\Http\Requests\Api\Application\ApplicationApiRequest;

class GetExternalUserRequest extends ApplicationApiRequest
{
    /**
     * @var User
     */
    private $userModel;

    /**
     * @var string
     */
    protected $resource = AdminAcl::RESOURCE_USERS;

    /**
     * @var int
     */
    protected $permission = AdminAcl::READ;

    /**
     * Determine if the requested external user exists.
     *
     * @return bool
     */
    public function resourceExists(): bool
    {
        $repository = $this->container->make(UserRepositoryInterface::class);

        try {
            $this->userModel = $repository->findFirstWhere([
                ['external_id', '=', $this->route()->parameter('external_id')],
            ]);
        } catch (RecordNotFoundException $exception) {
            return false;
        }

        return true;
    }

    /**
     * Return the user model for the requested external user.
     * @return \Amghost\Models\User
     */
    public function getUserModel(): User
    {
        return $this->userModel;
    }
}
