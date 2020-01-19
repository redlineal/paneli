<?php
/**
 * AMGHOST - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Services\Servers;

use Psr\Log\LoggerInterface as Writer;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Database\ConnectionInterface;
use Amghost\Services\Databases\DatabaseManagementService;
use Amghost\Contracts\Repository\ServerRepositoryInterface;
use Amghost\Contracts\Repository\DatabaseRepositoryInterface;
use Amghost\Exceptions\Http\Connection\DaemonConnectionException;
use Amghost\Contracts\Repository\Daemon\ServerRepositoryInterface as DaemonServerRepositoryInterface;

class ServerDeletionService
{
    /**
     * @var \Illuminate\Database\ConnectionInterface
     */
    protected $connection;

    /**
     * @var \Amghost\Contracts\Repository\Daemon\ServerRepositoryInterface
     */
    protected $daemonServerRepository;

    /**
     * @var \Amghost\Services\Databases\DatabaseManagementService
     */
    protected $databaseManagementService;

    /**
     * @var \Amghost\Contracts\Repository\DatabaseRepositoryInterface
     */
    protected $databaseRepository;

    /**
     * @var bool
     */
    protected $force = false;

    /**
     * @var \Amghost\Contracts\Repository\ServerRepositoryInterface
     */
    protected $repository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $writer;

    /**
     * DeletionService constructor.
     *
     * @param \Illuminate\Database\ConnectionInterface                           $connection
     * @param \Amghost\Contracts\Repository\Daemon\ServerRepositoryInterface $daemonServerRepository
     * @param \Amghost\Contracts\Repository\DatabaseRepositoryInterface      $databaseRepository
     * @param \Amghost\Services\Databases\DatabaseManagementService          $databaseManagementService
     * @param \Amghost\Contracts\Repository\ServerRepositoryInterface        $repository
     * @param \Psr\Log\LoggerInterface                                           $writer
     */
    public function __construct(
        ConnectionInterface $connection,
        DaemonServerRepositoryInterface $daemonServerRepository,
        DatabaseRepositoryInterface $databaseRepository,
        DatabaseManagementService $databaseManagementService,
        ServerRepositoryInterface $repository,
        Writer $writer
    ) {
        $this->daemonServerRepository = $daemonServerRepository;
        $this->connection = $connection;
        $this->databaseManagementService = $databaseManagementService;
        $this->databaseRepository = $databaseRepository;
        $this->repository = $repository;
        $this->writer = $writer;
    }

    /**
     * Set if the server should be forcibly deleted from the panel (ignoring daemon errors) or not.
     *
     * @param bool $bool
     * @return $this
     */
    public function withForce($bool = true)
    {
        $this->force = $bool;

        return $this;
    }

    /**
     * Delete a server from the panel and remove any associated databases from hosts.
     *
     * @param int|\Amghost\Models\Server $server
     *
     * @throws \Amghost\Exceptions\DisplayException
     */
    public function handle($server)
    {
        try {
            $this->daemonServerRepository->setServer($server)->delete();
        } catch (RequestException $exception) {
            $response = $exception->getResponse();

            if (is_null($response) || (! is_null($response) && $response->getStatusCode() !== 404)) {
                // If not forcing the deletion, throw an exception, otherwise just log it and
                // continue with server deletion process in the panel.
                if (! $this->force) {
                    throw new DaemonConnectionException($exception);
                } else {
                    $this->writer->warning($exception);
                }
            }
        }

        $this->connection->beginTransaction();
        $this->databaseRepository->setColumns('id')->findWhere([['server_id', '=', $server->id]])->each(function ($item) {
            $this->databaseManagementService->delete($item->id);
        });

        $this->repository->delete($server->id);
        $this->connection->commit();
    }
}
