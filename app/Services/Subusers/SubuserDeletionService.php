<?php
/**
 * AMGHOST - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Services\Subusers;

use Amghost\Models\Subuser;
use Illuminate\Database\ConnectionInterface;
use Amghost\Services\DaemonKeys\DaemonKeyDeletionService;
use Amghost\Contracts\Repository\SubuserRepositoryInterface;

class SubuserDeletionService
{
    /**
     * @var \Illuminate\Database\ConnectionInterface
     */
    private $connection;

    /**
     * @var \Amghost\Services\DaemonKeys\DaemonKeyDeletionService
     */
    private $keyDeletionService;

    /**
     * @var \Amghost\Contracts\Repository\SubuserRepositoryInterface
     */
    private $repository;

    /**
     * SubuserDeletionService constructor.
     *
     * @param \Illuminate\Database\ConnectionInterface                     $connection
     * @param \Amghost\Services\DaemonKeys\DaemonKeyDeletionService    $keyDeletionService
     * @param \Amghost\Contracts\Repository\SubuserRepositoryInterface $repository
     */
    public function __construct(
        ConnectionInterface $connection,
        DaemonKeyDeletionService $keyDeletionService,
        SubuserRepositoryInterface $repository
    ) {
        $this->connection = $connection;
        $this->keyDeletionService = $keyDeletionService;
        $this->repository = $repository;
    }

    /**
     * Delete a subuser and their associated permissions from the Panel and Daemon.
     *
     * @param \Amghost\Models\Subuser $subuser
     *
     * @throws \Amghost\Exceptions\DisplayException
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function handle(Subuser $subuser)
    {
        $this->connection->beginTransaction();
        $this->keyDeletionService->handle($subuser->server_id, $subuser->user_id);
        $this->repository->delete($subuser->id);
        $this->connection->commit();
    }
}
