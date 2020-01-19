<?php

namespace Amghost\Http\Controllers\Api\Application\Servers;

use Illuminate\Http\Response;
use Amghost\Models\Server;
use Illuminate\Http\JsonResponse;
use Amghost\Services\Servers\ServerCreationService;
use Amghost\Services\Servers\ServerDeletionService;
use Amghost\Contracts\Repository\ServerRepositoryInterface;
use Amghost\Transformers\Api\Application\ServerTransformer;
use Amghost\Http\Requests\Api\Application\Servers\GetServerRequest;
use Amghost\Http\Requests\Api\Application\Servers\GetServersRequest;
use Amghost\Http\Requests\Api\Application\Servers\ServerWriteRequest;
use Amghost\Http\Requests\Api\Application\Servers\StoreServerRequest;
use Amghost\Http\Controllers\Api\Application\ApplicationApiController;

class ServerController extends ApplicationApiController
{
    /**
     * @var \Amghost\Services\Servers\ServerCreationService
     */
    private $creationService;

    /**
     * @var \Amghost\Services\Servers\ServerDeletionService
     */
    private $deletionService;

    /**
     * @var \Amghost\Contracts\Repository\ServerRepositoryInterface
     */
    private $repository;

    /**
     * ServerController constructor.
     *
     * @param \Amghost\Services\Servers\ServerCreationService         $creationService
     * @param \Amghost\Services\Servers\ServerDeletionService         $deletionService
     * @param \Amghost\Contracts\Repository\ServerRepositoryInterface $repository
     */
    public function __construct(
        ServerCreationService $creationService,
        ServerDeletionService $deletionService,
        ServerRepositoryInterface $repository
    ) {
        parent::__construct();

        $this->creationService = $creationService;
        $this->deletionService = $deletionService;
        $this->repository = $repository;
    }

    /**
     * Return all of the servers that currently exist on the Panel.
     *
     * @param \Amghost\Http\Requests\Api\Application\Servers\GetServersRequest $request
     * @return array
     */
    public function index(GetServersRequest $request): array
    {
        $servers = $this->repository->setSearchTerm($request->input('search'))->paginated(50);

        return $this->fractal->collection($servers)
            ->transformWith($this->getTransformer(ServerTransformer::class))
            ->toArray();
    }

    /**
     * Create a new server on the system.
     *
     * @param \Amghost\Http\Requests\Api\Application\Servers\StoreServerRequest $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Amghost\Exceptions\DisplayException
     * @throws \Amghost\Exceptions\Http\Connection\DaemonConnectionException
     * @throws \Amghost\Exceptions\Model\DataValidationException
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     * @throws \Amghost\Exceptions\Service\Deployment\NoViableAllocationException
     * @throws \Amghost\Exceptions\Service\Deployment\NoViableNodeException
     */
    public function store(StoreServerRequest $request): JsonResponse
    {
        $server = $this->creationService->handle($request->validated(), $request->getDeploymentObject());

        return $this->fractal->item($server)
            ->transformWith($this->getTransformer(ServerTransformer::class))
            ->respond(201);
    }

    /**
     * Show a single server transformed for the application API.
     *
     * @param \Amghost\Http\Requests\Api\Application\Servers\GetServerRequest $request
     * @return array
     */
    public function view(GetServerRequest $request): array
    {
        return $this->fractal->item($request->getModel(Server::class))
            ->transformWith($this->getTransformer(ServerTransformer::class))
            ->toArray();
    }

    /**
     * @param \Amghost\Http\Requests\Api\Application\Servers\ServerWriteRequest $request
     * @param \Amghost\Models\Server                                            $server
     * @param string                                                                $force
     * @return \Illuminate\Http\Response
     *
     * @throws \Amghost\Exceptions\DisplayException
     */
    public function delete(ServerWriteRequest $request, Server $server, string $force = ''): Response
    {
        $this->deletionService->withForce($force === 'force')->handle($server);

        return $this->returnNoContent();
    }
}
