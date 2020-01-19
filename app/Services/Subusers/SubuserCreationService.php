<?php
/**
 * AMGHOST - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Services\Subusers;

use Amghost\Models\Server;
use Illuminate\Database\ConnectionInterface;
use Amghost\Services\Users\UserCreationService;
use Amghost\Contracts\Repository\UserRepositoryInterface;
use Amghost\Services\DaemonKeys\DaemonKeyCreationService;
use Amghost\Exceptions\Repository\RecordNotFoundException;
use Amghost\Contracts\Repository\ServerRepositoryInterface;
use Amghost\Contracts\Repository\SubuserRepositoryInterface;
use Amghost\Exceptions\Service\Subuser\UserIsServerOwnerException;
use Amghost\Exceptions\Service\Subuser\ServerSubuserExistsException;

class SubuserCreationService
{
    /**
     * @var \Illuminate\Database\ConnectionInterface
     */
    protected $connection;

    /**
     * @var \Amghost\Services\DaemonKeys\DaemonKeyCreationService
     */
    protected $keyCreationService;

    /**
     * @var \Amghost\Services\Subusers\PermissionCreationService
     */
    protected $permissionService;

    /**
     * @var \Amghost\Contracts\Repository\SubuserRepositoryInterface
     */
    protected $subuserRepository;

    /**
     * @var \Amghost\Contracts\Repository\ServerRepositoryInterface
     */
    protected $serverRepository;

    /**
     * @var \Amghost\Services\Users\UserCreationService
     */
    protected $userCreationService;

    /**
     * @var \Amghost\Contracts\Repository\UserRepositoryInterface
     */
    protected $userRepository;

    /**
     * SubuserCreationService constructor.
     *
     * @param \Illuminate\Database\ConnectionInterface                     $connection
     * @param \Amghost\Services\DaemonKeys\DaemonKeyCreationService    $keyCreationService
     * @param \Amghost\Services\Subusers\PermissionCreationService     $permissionService
     * @param \Amghost\Contracts\Repository\ServerRepositoryInterface  $serverRepository
     * @param \Amghost\Contracts\Repository\SubuserRepositoryInterface $subuserRepository
     * @param \Amghost\Services\Users\UserCreationService              $userCreationService
     * @param \Amghost\Contracts\Repository\UserRepositoryInterface    $userRepository
     */
    public function __construct(
        ConnectionInterface $connection,
        DaemonKeyCreationService $keyCreationService,
        PermissionCreationService $permissionService,
        ServerRepositoryInterface $serverRepository,
        SubuserRepositoryInterface $subuserRepository,
        UserCreationService $userCreationService,
        UserRepositoryInterface $userRepository
    ) {
        $this->connection = $connection;
        $this->keyCreationService = $keyCreationService;
        $this->permissionService = $permissionService;
        $this->serverRepository = $serverRepository;
        $this->subuserRepository = $subuserRepository;
        $this->userRepository = $userRepository;
        $this->userCreationService = $userCreationService;
    }

    /**
     * @param int|\Amghost\Models\Server $server
     * @param string                         $email
     * @param array                          $permissions
     * @return \Amghost\Models\Subuser
     *
     * @throws \Exception
     * @throws \Amghost\Exceptions\Model\DataValidationException
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     * @throws \Amghost\Exceptions\Service\Subuser\ServerSubuserExistsException
     * @throws \Amghost\Exceptions\Service\Subuser\UserIsServerOwnerException
     */
    public function handle($server, $email, array $permissions)
    {
        if (! $server instanceof Server) {
            $server = $this->serverRepository->find($server);
        }

        $this->connection->beginTransaction();
        try {
            $user = $this->userRepository->findFirstWhere([['email', '=', $email]]);

            if ($server->owner_id === $user->id) {
                throw new UserIsServerOwnerException(trans('exceptions.subusers.user_is_owner'));
            }

            $subuserCount = $this->subuserRepository->findCountWhere([['user_id', '=', $user->id], ['server_id', '=', $server->id]]);
            if ($subuserCount !== 0) {
                throw new ServerSubuserExistsException(trans('exceptions.subusers.subuser_exists'));
            }
        } catch (RecordNotFoundException $exception) {
            $username = preg_replace('/([^\w\.-]+)/', '', strtok($email, '@'));
            $user = $this->userCreationService->handle([
                'email' => $email,
                'username' => $username . str_random(3),
                'name_first' => 'Server',
                'name_last' => 'Subuser',
                'root_admin' => false,
            ]);
        }

        $subuser = $this->subuserRepository->create(['user_id' => $user->id, 'server_id' => $server->id]);
        $this->keyCreationService->handle($server->id, $user->id);
        $this->permissionService->handle($subuser->id, $permissions);
        $this->connection->commit();

        return $subuser;
    }
}
