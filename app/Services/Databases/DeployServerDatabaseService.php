<?php

namespace Amghost\Services\Databases;

use Amghost\Models\Server;
use Amghost\Models\Database;
use Amghost\Contracts\Repository\DatabaseRepositoryInterface;
use Amghost\Contracts\Repository\DatabaseHostRepositoryInterface;
use Amghost\Exceptions\Service\Database\TooManyDatabasesException;
use Amghost\Exceptions\Service\Database\NoSuitableDatabaseHostException;
use Amghost\Exceptions\Service\Database\DatabaseClientFeatureNotEnabledException;

class DeployServerDatabaseService
{
    /**
     * @var \Amghost\Contracts\Repository\DatabaseHostRepositoryInterface
     */
    private $databaseHostRepository;

    /**
     * @var \Amghost\Services\Databases\DatabaseManagementService
     */
    private $managementService;

    /**
     * @var \Amghost\Contracts\Repository\DatabaseRepositoryInterface
     */
    private $repository;

    /**
     * ServerDatabaseCreationService constructor.
     *
     * @param \Amghost\Contracts\Repository\DatabaseRepositoryInterface     $repository
     * @param \Amghost\Contracts\Repository\DatabaseHostRepositoryInterface $databaseHostRepository
     * @param \Amghost\Services\Databases\DatabaseManagementService         $managementService
     */
    public function __construct(
        DatabaseRepositoryInterface $repository,
        DatabaseHostRepositoryInterface $databaseHostRepository,
        DatabaseManagementService $managementService
    ) {
        $this->databaseHostRepository = $databaseHostRepository;
        $this->managementService = $managementService;
        $this->repository = $repository;
    }

    /**
     * @param \Amghost\Models\Server $server
     * @param array                      $data
     * @return \Amghost\Models\Database
     *
     * @throws \Amghost\Exceptions\Service\Database\DatabaseClientFeatureNotEnabledException
     * @throws \Exception
     */
    public function handle(Server $server, array $data): Database
    {
        if (! config('amghost.client_features.databases.enabled')) {
            throw new DatabaseClientFeatureNotEnabledException;
        }

        $databases = $this->repository->findCountWhere([['server_id', '=', $server->id]]);
        if (! is_null($server->database_limit) && $databases >= $server->database_limit) {
            throw new TooManyDatabasesException;
        }

        $allowRandom = config('amghost.client_features.databases.allow_random');
        $hosts = $this->databaseHostRepository->setColumns(['id'])->findWhere([
            ['node_id', '=', $server->node_id],
        ]);

        if ($hosts->isEmpty() && ! $allowRandom) {
            throw new NoSuitableDatabaseHostException;
        }

        if ($hosts->isEmpty()) {
            $hosts = $this->databaseHostRepository->setColumns(['id'])->all();
            if ($hosts->isEmpty()) {
                throw new NoSuitableDatabaseHostException;
            }
        }

        $host = $hosts->random();

        return $this->managementService->create($server->id, [
            'database_host_id' => $host->id,
            'database' => array_get($data, 'database'),
            'remote' => array_get($data, 'remote'),
        ]);
    }
}
