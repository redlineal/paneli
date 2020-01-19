<?php
/**
 * AMGHOST - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Services\Subusers;

use Amghost\Models\Subuser;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Database\ConnectionInterface;
use Amghost\Services\DaemonKeys\DaemonKeyProviderService;
use Amghost\Contracts\Repository\SubuserRepositoryInterface;
use Amghost\Contracts\Repository\PermissionRepositoryInterface;
use Amghost\Exceptions\Http\Connection\DaemonConnectionException;
use Amghost\Contracts\Repository\Daemon\ServerRepositoryInterface as DaemonServerRepositoryInterface;

class SubuserUpdateService
{
    /**
     * @var \Illuminate\Database\ConnectionInterface
     */
    private $connection;

    /**
     * @var \Amghost\Contracts\Repository\Daemon\ServerRepositoryInterface
     */
    private $daemonRepository;

    /**
     * @var \Amghost\Services\DaemonKeys\DaemonKeyProviderService
     */
    private $keyProviderService;

    /**
     * @var \Amghost\Contracts\Repository\PermissionRepositoryInterface
     */
    private $permissionRepository;

    /**
     * @var \Amghost\Services\Subusers\PermissionCreationService
     */
    private $permissionService;

    /**
     * @var \Amghost\Contracts\Repository\SubuserRepositoryInterface
     */
    private $repository;

    /**
     * SubuserUpdateService constructor.
     *
     * @param \Illuminate\Database\ConnectionInterface                           $connection
     * @param \Amghost\Services\DaemonKeys\DaemonKeyProviderService          $keyProviderService
     * @param \Amghost\Contracts\Repository\Daemon\ServerRepositoryInterface $daemonRepository
     * @param \Amghost\Services\Subusers\PermissionCreationService           $permissionService
     * @param \Amghost\Contracts\Repository\PermissionRepositoryInterface    $permissionRepository
     * @param \Amghost\Contracts\Repository\SubuserRepositoryInterface       $repository
     */
    public function __construct(
        ConnectionInterface $connection,
        DaemonKeyProviderService $keyProviderService,
        DaemonServerRepositoryInterface $daemonRepository,
        PermissionCreationService $permissionService,
        PermissionRepositoryInterface $permissionRepository,
        SubuserRepositoryInterface $repository
    ) {
        $this->connection = $connection;
        $this->daemonRepository = $daemonRepository;
        $this->keyProviderService = $keyProviderService;
        $this->permissionRepository = $permissionRepository;
        $this->permissionService = $permissionService;
        $this->repository = $repository;
    }

    /**
     * Update permissions for a given subuser.
     *
     * @param \Amghost\Models\Subuser $subuser
     * @param array                       $permissions
     *
     * @throws \Amghost\Exceptions\Http\Connection\DaemonConnectionException
     * @throws \Amghost\Exceptions\Model\DataValidationException
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function handle(Subuser $subuser, array $permissions)
    {
        $subuser = $this->repository->loadServerAndUserRelations($subuser);

        $this->connection->beginTransaction();
        $this->permissionRepository->deleteWhere([['subuser_id', '=', $subuser->id]]);
        $this->permissionService->handle($subuser->id, $permissions);

        try {
            $token = $this->keyProviderService->handle($subuser->getRelation('server'), $subuser->getRelation('user'), false);
            $this->daemonRepository->setServer($subuser->getRelation('server'))->revokeAccessKey($token);
        } catch (RequestException $exception) {
            $this->connection->rollBack();
            throw new DaemonConnectionException($exception);
        }

        $this->connection->commit();
    }
}
