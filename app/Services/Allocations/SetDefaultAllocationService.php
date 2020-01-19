<?php

namespace Amghost\Services\Allocations;

use Amghost\Models\Server;
use Amghost\Models\Allocation;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Database\ConnectionInterface;
use Amghost\Contracts\Repository\ServerRepositoryInterface;
use Amghost\Contracts\Repository\AllocationRepositoryInterface;
use Amghost\Exceptions\Http\Connection\DaemonConnectionException;
use Amghost\Exceptions\Service\Allocation\AllocationDoesNotBelongToServerException;
use Amghost\Contracts\Repository\Daemon\ServerRepositoryInterface as DaemonRepositoryInterface;

class SetDefaultAllocationService
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
     * @var \Amghost\Contracts\Repository\AllocationRepositoryInterface
     */
    private $repository;

    /**
     * @var \Amghost\Contracts\Repository\ServerRepositoryInterface
     */
    private $serverRepository;

    /**
     * SetDefaultAllocationService constructor.
     *
     * @param \Amghost\Contracts\Repository\AllocationRepositoryInterface    $repository
     * @param \Illuminate\Database\ConnectionInterface                           $connection
     * @param \Amghost\Contracts\Repository\Daemon\ServerRepositoryInterface $daemonRepository
     * @param \Amghost\Contracts\Repository\ServerRepositoryInterface        $serverRepository
     */
    public function __construct(
        AllocationRepositoryInterface $repository,
        ConnectionInterface $connection,
        DaemonRepositoryInterface $daemonRepository,
        ServerRepositoryInterface $serverRepository
    ) {
        $this->connection = $connection;
        $this->daemonRepository = $daemonRepository;
        $this->repository = $repository;
        $this->serverRepository = $serverRepository;
    }

    /**
     * Update the default allocation for a server only if that allocation is currently
     * assigned to the specified server.
     *
     * @param int|\Amghost\Models\Server $server
     * @param int                            $allocation
     * @return \Amghost\Models\Allocation
     *
     * @throws \Amghost\Exceptions\Http\Connection\DaemonConnectionException
     * @throws \Amghost\Exceptions\Model\DataValidationException
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     * @throws \Amghost\Exceptions\Service\Allocation\AllocationDoesNotBelongToServerException
     */
    public function handle($server, int $allocation): Allocation
    {
        if (! $server instanceof Server) {
            $server = $this->serverRepository->find($server);
        }

        $allocations = $this->repository->findWhere([['server_id', '=', $server->id]]);
        $model = $allocations->filter(function ($model) use ($allocation) {
            return $model->id === $allocation;
        })->first();

        if (! $model instanceof Allocation) {
            throw new AllocationDoesNotBelongToServerException;
        }

        $this->connection->beginTransaction();
        $this->serverRepository->withoutFreshModel()->update($server->id, ['allocation_id' => $model->id]);

        // Update on the daemon.
        try {
            $this->daemonRepository->setServer($server)->update([
                'build' => [
                    'default' => [
                        'ip' => $model->ip,
                        'port' => $model->port,
                    ],
                    'ports|overwrite' => $allocations->groupBy('ip')->map(function ($item) {
                        return $item->pluck('port');
                    })->toArray(),
                ],
            ]);

            $this->connection->commit();
        } catch (RequestException $exception) {
            $this->connection->rollBack();
            throw new DaemonConnectionException($exception);
        }

        return $model;
    }
}
