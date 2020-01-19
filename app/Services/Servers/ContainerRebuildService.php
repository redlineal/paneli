<?php

namespace Amghost\Services\Servers;

use Amghost\Models\Server;
use GuzzleHttp\Exception\RequestException;
use Amghost\Exceptions\Http\Connection\DaemonConnectionException;
use Amghost\Contracts\Repository\Daemon\ServerRepositoryInterface;

class ContainerRebuildService
{
    /**
     * @var \Amghost\Contracts\Repository\ServerRepositoryInterface
     */
    private $repository;

    /**
     * ContainerRebuildService constructor.
     *
     * @param \Amghost\Contracts\Repository\Daemon\ServerRepositoryInterface $repository
     */
    public function __construct(ServerRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Mark a server for rebuild on next boot cycle.
     *
     * @param \Amghost\Models\Server $server
     *
     * @throws \Amghost\Exceptions\Http\Connection\DaemonConnectionException
     */
    public function handle(Server $server)
    {
        try {
            $this->repository->setServer($server)->rebuild();
        } catch (RequestException $exception) {
            throw new DaemonConnectionException($exception);
        }
    }
}
