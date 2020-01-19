<?php

namespace Amghost\Services\Servers;

use Ramsey\Uuid\Uuid;
use Amghost\Models\Node;
use Amghost\Models\User;
use Amghost\Models\Server;
use Illuminate\Support\Collection;
use Amghost\Models\Allocation;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Database\ConnectionInterface;
use Amghost\Models\Objects\DeploymentObject;
use Amghost\Services\Deployment\FindViableNodesService;
use Amghost\Contracts\Repository\EggRepositoryInterface;
use Amghost\Contracts\Repository\ServerRepositoryInterface;
use Amghost\Services\Deployment\AllocationSelectionService;
use Amghost\Contracts\Repository\AllocationRepositoryInterface;
use Amghost\Exceptions\Http\Connection\DaemonConnectionException;
use Amghost\Contracts\Repository\ServerVariableRepositoryInterface;
use Amghost\Contracts\Repository\Daemon\ServerRepositoryInterface as DaemonServerRepositoryInterface;

class ServerCreationService
{
    /**
     * @var \Amghost\Contracts\Repository\AllocationRepositoryInterface
     */
    private $allocationRepository;

    /**
     * @var \Amghost\Services\Deployment\AllocationSelectionService
     */
    private $allocationSelectionService;

    /**
     * @var \Amghost\Services\Servers\ServerConfigurationStructureService
     */
    private $configurationStructureService;

    /**
     * @var \Illuminate\Database\ConnectionInterface
     */
    private $connection;

    /**
     * @var \Amghost\Contracts\Repository\Daemon\ServerRepositoryInterface
     */
    private $daemonServerRepository;

    /**
     * @var \Amghost\Contracts\Repository\EggRepositoryInterface
     */
    private $eggRepository;

    /**
     * @var \Amghost\Services\Deployment\FindViableNodesService
     */
    private $findViableNodesService;

    /**
     * @var \Amghost\Contracts\Repository\ServerRepositoryInterface
     */
    private $repository;

    /**
     * @var \Amghost\Contracts\Repository\ServerVariableRepositoryInterface
     */
    private $serverVariableRepository;

    /**
     * @var \Amghost\Services\Servers\VariableValidatorService
     */
    private $validatorService;

    /**
     * CreationService constructor.
     *
     * @param \Amghost\Contracts\Repository\AllocationRepositoryInterface     $allocationRepository
     * @param \Amghost\Services\Deployment\AllocationSelectionService         $allocationSelectionService
     * @param \Illuminate\Database\ConnectionInterface                            $connection
     * @param \Amghost\Contracts\Repository\Daemon\ServerRepositoryInterface  $daemonServerRepository
     * @param \Amghost\Contracts\Repository\EggRepositoryInterface            $eggRepository
     * @param \Amghost\Services\Deployment\FindViableNodesService             $findViableNodesService
     * @param \Amghost\Services\Servers\ServerConfigurationStructureService   $configurationStructureService
     * @param \Amghost\Contracts\Repository\ServerRepositoryInterface         $repository
     * @param \Amghost\Contracts\Repository\ServerVariableRepositoryInterface $serverVariableRepository
     * @param \Amghost\Services\Servers\VariableValidatorService              $validatorService
     */
    public function __construct(
        AllocationRepositoryInterface $allocationRepository,
        AllocationSelectionService $allocationSelectionService,
        ConnectionInterface $connection,
        DaemonServerRepositoryInterface $daemonServerRepository,
        EggRepositoryInterface $eggRepository,
        FindViableNodesService $findViableNodesService,
        ServerConfigurationStructureService $configurationStructureService,
        ServerRepositoryInterface $repository,
        ServerVariableRepositoryInterface $serverVariableRepository,
        VariableValidatorService $validatorService
    ) {
        $this->allocationSelectionService = $allocationSelectionService;
        $this->allocationRepository = $allocationRepository;
        $this->configurationStructureService = $configurationStructureService;
        $this->connection = $connection;
        $this->daemonServerRepository = $daemonServerRepository;
        $this->eggRepository = $eggRepository;
        $this->findViableNodesService = $findViableNodesService;
        $this->repository = $repository;
        $this->serverVariableRepository = $serverVariableRepository;
        $this->validatorService = $validatorService;
    }

    /**
     * Create a server on the Panel and trigger a request to the Daemon to begin the server
     * creation process. This function will attempt to set as many additional values
     * as possible given the input data. For example, if an allocation_id is passed with
     * no node_id the node_is will be picked from the allocation.
     *
     * @param array                                             $data
     * @param \Amghost\Models\Objects\DeploymentObject|null $deployment
     * @return \Amghost\Models\Server
     *
     * @throws \Amghost\Exceptions\DisplayException
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Amghost\Exceptions\Model\DataValidationException
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     * @throws \Amghost\Exceptions\Service\Deployment\NoViableNodeException
     * @throws \Amghost\Exceptions\Http\Connection\DaemonConnectionException
     * @throws \Amghost\Exceptions\Service\Deployment\NoViableAllocationException
     */
    public function handle(array $data, DeploymentObject $deployment = null): Server
    {
        $this->connection->beginTransaction();

        // If a deployment object has been passed we need to get the allocation
        // that the server should use, and assign the node from that allocation.
        if ($deployment instanceof DeploymentObject) {
            $allocation = $this->configureDeployment($data, $deployment);
            $data['allocation_id'] = $allocation->id;
            $data['node_id'] = $allocation->node_id;
        }

        // Auto-configure the node based on the selected allocation
        // if no node was defined.
        if (is_null(array_get($data, 'node_id'))) {
            $data['node_id'] = $this->getNodeFromAllocation($data['allocation_id']);
        }

        if (is_null(array_get($data, 'nest_id'))) {
            $egg = $this->eggRepository->setColumns(['id', 'nest_id'])->find(array_get($data, 'egg_id'));
            $data['nest_id'] = $egg->nest_id;
        }

        $eggVariableData = $this->validatorService
            ->setUserLevel(User::USER_LEVEL_ADMIN)
            ->handle(array_get($data, 'egg_id'), array_get($data, 'environment', []));

        // Create the server and assign any additional allocations to it.
        $server = $this->createModel($data);
        $this->storeAssignedAllocations($server, $data);
        $this->storeEggVariables($server, $eggVariableData);

        $structure = $this->configurationStructureService->handle($server);

        try {
            $this->daemonServerRepository->setServer($server)->create($structure, [
                'start_on_completion' => (bool) array_get($data, 'start_on_completion', false),
            ]);

            $this->connection->commit();
        } catch (RequestException $exception) {
            $this->connection->rollBack();
            throw new DaemonConnectionException($exception);
        }

        return $server;
    }

    /**
     * Gets an allocation to use for automatic deployment.
     *
     * @param array                                        $data
     * @param \Amghost\Models\Objects\DeploymentObject $deployment
     *
     * @return \Amghost\Models\Allocation
     * @throws \Amghost\Exceptions\DisplayException
     * @throws \Amghost\Exceptions\Service\Deployment\NoViableAllocationException
     * @throws \Amghost\Exceptions\Service\Deployment\NoViableNodeException
     */
    private function configureDeployment(array $data, DeploymentObject $deployment): Allocation
    {
        $nodes = $this->findViableNodesService->setLocations($deployment->getLocations())
            ->setDisk(array_get($data, 'disk'))
            ->setMemory(array_get($data, 'memory'))
            ->handle();

        return $this->allocationSelectionService->setDedicated($deployment->isDedicated())
            ->setNodes($nodes)
            ->setPorts($deployment->getPorts())
            ->handle();
    }

    /**
     * Store the server in the database and return the model.
     *
     * @param array $data
     * @return \Amghost\Models\Server
     *
     * @throws \Amghost\Exceptions\Model\DataValidationException
     */
    private function createModel(array $data): Server
    {
        $uuid = $this->generateUniqueUuidCombo();

        return $this->repository->create([
            'external_id' => array_get($data, 'external_id'),
            'uuid' => $uuid,
            'uuidShort' => substr($uuid, 0, 8),
            'node_id' => array_get($data, 'node_id'),
            'name' => array_get($data, 'name'),
            'description' => array_get($data, 'description') ?? '',
            'skip_scripts' => array_get($data, 'skip_scripts') ?? isset($data['skip_scripts']),
            'suspended' => false,
            'owner_id' => array_get($data, 'owner_id'),
            'memory' => array_get($data, 'memory'),
            'swap' => array_get($data, 'swap'),
            'disk' => array_get($data, 'disk'),
            'io' => array_get($data, 'io'),
            'cpu' => array_get($data, 'cpu'),
            'oom_disabled' => array_get($data, 'oom_disabled', true),
            'allocation_id' => array_get($data, 'allocation_id'),
            'nest_id' => array_get($data, 'nest_id'),
            'egg_id' => array_get($data, 'egg_id'),
            'pack_id' => (! isset($data['pack_id']) || $data['pack_id'] == 0) ? null : $data['pack_id'],
            'startup' => array_get($data, 'startup'),
            'daemonSecret' => str_random(Node::DAEMON_SECRET_LENGTH),
            'image' => array_get($data, 'image'),
            'database_limit' => array_get($data, 'database_limit'),
            'allocation_limit' => array_get($data, 'allocation_limit'),
        ]);
    }

    /**
     * Configure the allocations assigned to this server.
     *
     * @param \Amghost\Models\Server $server
     * @param array                      $data
     */
    private function storeAssignedAllocations(Server $server, array $data)
    {
        $records = [$data['allocation_id']];
        if (isset($data['allocation_additional']) && is_array($data['allocation_additional'])) {
            $records = array_merge($records, $data['allocation_additional']);
        }

        $this->allocationRepository->assignAllocationsToServer($server->id, $records);
    }

    /**
     * Process environment variables passed for this server and store them in the database.
     *
     * @param \Amghost\Models\Server     $server
     * @param \Illuminate\Support\Collection $variables
     */
    private function storeEggVariables(Server $server, Collection $variables)
    {
        $records = $variables->map(function ($result) use ($server) {
            return [
                'server_id' => $server->id,
                'variable_id' => $result->id,
                'variable_value' => $result->value,
            ];
        })->toArray();

        if (! empty($records)) {
            $this->serverVariableRepository->insert($records);
        }
    }

    /**
     * Get the node that an allocation belongs to.
     *
     * @param int $allocation
     * @return int
     *
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    private function getNodeFromAllocation(int $allocation): int
    {
        $allocation = $this->allocationRepository->setColumns(['id', 'node_id'])->find($allocation);

        return $allocation->node_id;
    }

    /**
     * Create a unique UUID and UUID-Short combo for a server.
     *
     * @return string
     */
    private function generateUniqueUuidCombo(): string
    {
        $uuid = Uuid::uuid4()->toString();

        if (! $this->repository->isUniqueUuidCombo($uuid, substr($uuid, 0, 8))) {
            return $this->generateUniqueUuidCombo();
        }

        return $uuid;
    }
}
