<?php

namespace Amghost\Services\Sftp;

use Amghost\Contracts\Repository\UserRepositoryInterface;
use Amghost\Services\DaemonKeys\DaemonKeyProviderService;
use Amghost\Exceptions\Repository\RecordNotFoundException;
use Amghost\Contracts\Repository\ServerRepositoryInterface;
use Amghost\Contracts\Repository\SubuserRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AuthenticateUsingPasswordService
{
    /**
     * @var \Amghost\Services\DaemonKeys\DaemonKeyProviderService
     */
    private $keyProviderService;

    /**
     * @var \Amghost\Contracts\Repository\ServerRepositoryInterface
     */
    private $repository;

    /**
     * @var \Amghost\Contracts\Repository\UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var \Amghost\Contracts\Repository\SubuserRepositoryInterface
     */
    private $subuserRepository;

    /**
     * AuthenticateUsingPasswordService constructor.
     *
     * @param \Amghost\Services\DaemonKeys\DaemonKeyProviderService    $keyProviderService
     * @param \Amghost\Contracts\Repository\ServerRepositoryInterface  $repository
     * @param \Amghost\Contracts\Repository\SubuserRepositoryInterface $subuserRepository
     * @param \Amghost\Contracts\Repository\UserRepositoryInterface    $userRepository
     */
    public function __construct(
        DaemonKeyProviderService $keyProviderService,
        ServerRepositoryInterface $repository,
        SubuserRepositoryInterface $subuserRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->keyProviderService = $keyProviderService;
        $this->repository = $repository;
        $this->subuserRepository = $subuserRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Attempt to authenticate a provided username and password and determine if they
     * have permission to access a given server. This function does not account for
     * subusers currently. Only administrators and server owners can login to access
     * their files at this time.
     *
     * Server must exist on the node that the API call is being made from in order for a
     * valid response to be provided.
     *
     * @param string      $username
     * @param string      $password
     * @param int         $node
     * @param string|null $server
     * @return array
     *
     * @throws \Amghost\Exceptions\Model\DataValidationException
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    public function handle(string $username, string $password, int $node, string $server = null): array
    {
        if (is_null($server)) {
            throw new RecordNotFoundException;
        }

        $user = $this->userRepository->setColumns(['id', 'root_admin', 'password'])->findFirstWhere([['username', '=', $username]]);
        if (! password_verify($password, $user->password)) {
            throw new RecordNotFoundException;
        }

        $server = $this->repository->setColumns(['id', 'node_id', 'owner_id', 'uuid', 'installed', 'suspended'])->getByUuid($server);
        if ($server->node_id !== $node) {
            throw new RecordNotFoundException;
        }

        if (! $user->root_admin && $server->owner_id !== $user->id) {
            $subuser = $this->subuserRepository->getWithPermissionsUsingUserAndServer($user->id, $server->id);
            $permissions = $subuser->getRelation('permissions')->pluck('permission')->toArray();

            if (! in_array('access-sftp', $permissions)) {
                throw new RecordNotFoundException;
            }
        }

        if ($server->installed !== 1 || $server->suspended) {
            throw new BadRequestHttpException;
        }

        return [
            'server' => $server->uuid,
            'token' => $this->keyProviderService->handle($server, $user),
            'permissions' => $permissions ?? ['*'],
        ];
    }
}
