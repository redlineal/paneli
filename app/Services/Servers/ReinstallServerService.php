<?php
/**
 * AMGHOST - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Services\Servers;

use Amghost\Models\Server;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Database\ConnectionInterface;
use Amghost\Contracts\Repository\ServerRepositoryInterface;
use Amghost\Exceptions\Http\Connection\DaemonConnectionException;
use Amghost\Contracts\Repository\Daemon\ServerRepositoryInterface as DaemonServerRepositoryInterface;

class ReinstallServerService
{
    /**
     * @var \Amghost\Contracts\Repository\Daemon\ServerRepositoryInterface
     */
    protected $daemonServerRepository;

    /**
     * @var \Illuminate\Database\ConnectionInterface
     */
    protected $database;

    /**
     * @var \Amghost\Contracts\Repository\ServerRepositoryInterface
     */
    protected $repository;

    /**
     * ReinstallService constructor.
     *
     * @param \Illuminate\Database\ConnectionInterface                           $database
     * @param \Amghost\Contracts\Repository\Daemon\ServerRepositoryInterface $daemonServerRepository
     * @param \Amghost\Contracts\Repository\ServerRepositoryInterface        $repository
     */
    public function __construct(
        ConnectionInterface $database,
        DaemonServerRepositoryInterface $daemonServerRepository,
        ServerRepositoryInterface $repository
    ) {
        $this->daemonServerRepository = $daemonServerRepository;
        $this->database = $database;
        $this->repository = $repository;
    }

    /**
     * @param int|\Amghost\Models\Server $server
     *
     * @throws \Amghost\Exceptions\DisplayException
     * @throws \Amghost\Exceptions\Model\DataValidationException
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function reinstall($server)
    {
        if (! $server instanceof Server) {
            $server = $this->repository->find($server);
        }

        $this->database->beginTransaction();
        $this->repository->withoutFreshModel()->update($server->id, [
            'installed' => 0,
        ], true, true);

        try {
            $this->daemonServerRepository->setServer($server)->reinstall();
            $this->database->commit();
        } catch (RequestException $exception) {
            throw new DaemonConnectionException($exception);
        }
    }
}
