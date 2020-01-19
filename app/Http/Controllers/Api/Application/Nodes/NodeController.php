<?php

namespace Amghost\Http\Controllers\Api\Application\Nodes;

use Amghost\Models\Node;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Amghost\Services\Nodes\NodeUpdateService;
use Amghost\Services\Nodes\NodeCreationService;
use Amghost\Services\Nodes\NodeDeletionService;
use Amghost\Contracts\Repository\NodeRepositoryInterface;
use Amghost\Transformers\Api\Application\NodeTransformer;
use Amghost\Http\Requests\Api\Application\Nodes\GetNodeRequest;
use Amghost\Http\Requests\Api\Application\Nodes\GetNodesRequest;
use Amghost\Http\Requests\Api\Application\Nodes\StoreNodeRequest;
use Amghost\Http\Requests\Api\Application\Nodes\DeleteNodeRequest;
use Amghost\Http\Requests\Api\Application\Nodes\UpdateNodeRequest;
use Amghost\Http\Controllers\Api\Application\ApplicationApiController;

class NodeController extends ApplicationApiController
{
    /**
     * @var \Amghost\Services\Nodes\NodeCreationService
     */
    private $creationService;

    /**
     * @var \Amghost\Services\Nodes\NodeDeletionService
     */
    private $deletionService;

    /**
     * @var \Amghost\Contracts\Repository\NodeRepositoryInterface
     */
    private $repository;

    /**
     * @var \Amghost\Services\Nodes\NodeUpdateService
     */
    private $updateService;

    /**
     * NodeController constructor.
     *
     * @param \Amghost\Services\Nodes\NodeCreationService           $creationService
     * @param \Amghost\Services\Nodes\NodeDeletionService           $deletionService
     * @param \Amghost\Services\Nodes\NodeUpdateService             $updateService
     * @param \Amghost\Contracts\Repository\NodeRepositoryInterface $repository
     */
    public function __construct(
        NodeCreationService $creationService,
        NodeDeletionService $deletionService,
        NodeUpdateService $updateService,
        NodeRepositoryInterface $repository
    ) {
        parent::__construct();

        $this->repository = $repository;
        $this->creationService = $creationService;
        $this->deletionService = $deletionService;
        $this->updateService = $updateService;
    }

    /**
     * Return all of the nodes currently available on the Panel.
     *
     * @param \Amghost\Http\Requests\Api\Application\Nodes\GetNodesRequest $request
     * @return array
     */
    public function index(GetNodesRequest $request): array
    {
        $nodes = $this->repository->setSearchTerm($request->input('search'))->paginated(50);

        return $this->fractal->collection($nodes)
            ->transformWith($this->getTransformer(NodeTransformer::class))
            ->toArray();
    }

    /**
     * Return data for a single instance of a node.
     *
     * @param \Amghost\Http\Requests\Api\Application\Nodes\GetNodeRequest $request
     * @return array
     */
    public function view(GetNodeRequest $request): array
    {
        return $this->fractal->item($request->getModel(Node::class))
            ->transformWith($this->getTransformer(NodeTransformer::class))
            ->toArray();
    }

    /**
     * Create a new node on the Panel. Returns the created node and a HTTP/201
     * status response on success.
     *
     * @param \Amghost\Http\Requests\Api\Application\Nodes\StoreNodeRequest $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Amghost\Exceptions\Model\DataValidationException
     */
    public function store(StoreNodeRequest $request): JsonResponse
    {
        $node = $this->creationService->handle($request->validated());

        return $this->fractal->item($node)
            ->transformWith($this->getTransformer(NodeTransformer::class))
            ->addMeta([
                'resource' => route('api.application.nodes.view', [
                    'node' => $node->id,
                ]),
            ])
            ->respond(201);
    }

    /**
     * Update an existing node on the Panel.
     *
     * @param \Amghost\Http\Requests\Api\Application\Nodes\UpdateNodeRequest $request
     * @return array
     *
     * @throws \Amghost\Exceptions\DisplayException
     * @throws \Amghost\Exceptions\Model\DataValidationException
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function update(UpdateNodeRequest $request): array
    {
        $node = $this->updateService->handle(
            $request->getModel(Node::class), $request->validated(), $request->input('reset_secret') === true
        );

        return $this->fractal->item($node)
            ->transformWith($this->getTransformer(NodeTransformer::class))
            ->toArray();
    }

    /**
     * Deletes a given node from the Panel as long as there are no servers
     * currently attached to it.
     *
     * @param \Amghost\Http\Requests\Api\Application\Nodes\DeleteNodeRequest $request
     * @return \Illuminate\Http\Response
     *
     * @throws \Amghost\Exceptions\Service\HasActiveServersException
     */
    public function delete(DeleteNodeRequest $request): Response
    {
        $this->deletionService->handle($request->getModel(Node::class));

        return response('', 204);
    }
}
