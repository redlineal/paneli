<?php

namespace Amghost\Services\Servers;

use Amghost\Models\Server;
use Illuminate\Database\ConnectionInterface;
use Amghost\Traits\Services\ReturnsUpdatedModels;
use Amghost\Repositories\Eloquent\ServerRepository;
use Amghost\Services\DaemonKeys\DaemonKeyCreationService;
use Amghost\Services\DaemonKeys\DaemonKeyDeletionService;

class DetailsModificationService
{
    use ReturnsUpdatedModels;

    /**
     * @var \Illuminate\Database\ConnectionInterface
     */
    private $connection;

    /**
     * @var \Amghost\Services\DaemonKeys\DaemonKeyCreationService
     */
    private $keyCreationService;

    /**
     * @var \Amghost\Services\DaemonKeys\DaemonKeyDeletionService
     */
    private $keyDeletionService;

    /**
     * @var \Amghost\Repositories\Eloquent\ServerRepository
     */
    private $repository;

    /**
     * DetailsModificationService constructor.
     *
     * @param \Illuminate\Database\ConnectionInterface                  $connection
     * @param \Amghost\Services\DaemonKeys\DaemonKeyCreationService $keyCreationService
     * @param \Amghost\Services\DaemonKeys\DaemonKeyDeletionService $keyDeletionService
     * @param \Amghost\Repositories\Eloquent\ServerRepository       $repository
     */
    public function __construct(
        ConnectionInterface $connection,
        DaemonKeyCreationService $keyCreationService,
        DaemonKeyDeletionService $keyDeletionService,
        ServerRepository $repository
    ) {
        $this->connection = $connection;
        $this->keyCreationService = $keyCreationService;
        $this->keyDeletionService = $keyDeletionService;
        $this->repository = $repository;
    }

    /**
     * Update the details for a single server instance.
     *
     * @param \Amghost\Models\Server $server
     * @param array                      $data
     * @return bool|\Amghost\Models\Server
     *
     * @throws \Amghost\Exceptions\DisplayException
     * @throws \Amghost\Exceptions\Model\DataValidationException
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function handle(Server $server, array $data)
    {
        $this->connection->beginTransaction();

        $response = $this->repository->setFreshModel($this->getUpdatedModel())->update($server->id, [
            'external_id' => array_get($data, 'external_id'),
            'owner_id' => array_get($data, 'owner_id'),
            'name' => array_get($data, 'name'),
            'description' => array_get($data, 'description') ?? '',
        ], true, true);

        if ((int) array_get($data, 'owner_id', 0) !== (int) $server->owner_id) {
            $this->keyDeletionService->handle($server, $server->owner_id);
            $this->keyCreationService->handle($server->id, array_get($data, 'owner_id'));
        }

        $this->connection->commit();

        return $response;
    }
}
