<?php

namespace Amghost\Http\Controllers\Api\Application\Nodes;

use Amghost\Models\Node;
use Illuminate\Http\Response;
use Amghost\Models\Allocation;
use Amghost\Services\Allocations\AssignmentService;
use Amghost\Services\Allocations\AllocationDeletionService;
use Amghost\Contracts\Repository\AllocationRepositoryInterface;
use Amghost\Transformers\Api\Application\AllocationTransformer;
use Amghost\Http\Controllers\Api\Application\ApplicationApiController;
use Amghost\Http\Requests\Api\Application\Allocations\GetAllocationsRequest;
use Amghost\Http\Requests\Api\Application\Allocations\StoreAllocationRequest;
use Amghost\Http\Requests\Api\Application\Allocations\DeleteAllocationRequest;

class AllocationController extends ApplicationApiController
{
    /**
     * @var \Amghost\Services\Allocations\AssignmentService
     */
    private $assignmentService;

    /**
     * @var \Amghost\Services\Allocations\AllocationDeletionService
     */
    private $deletionService;

    /**
     * @var \Amghost\Contracts\Repository\AllocationRepositoryInterface
     */
    private $repository;

    /**
     * AllocationController constructor.
     *
     * @param \Amghost\Services\Allocations\AssignmentService             $assignmentService
     * @param \Amghost\Services\Allocations\AllocationDeletionService     $deletionService
     * @param \Amghost\Contracts\Repository\AllocationRepositoryInterface $repository
     */
    public function __construct(
        AssignmentService $assignmentService,
        AllocationDeletionService $deletionService,
        AllocationRepositoryInterface $repository
    ) {
        parent::__construct();

        $this->assignmentService = $assignmentService;
        $this->deletionService = $deletionService;
        $this->repository = $repository;
    }

    /**
     * Return all of the allocations that exist for a given node.
     *
     * @param \Amghost\Http\Requests\Api\Application\Allocations\GetAllocationsRequest $request
     * @return array
     */
    public function index(GetAllocationsRequest $request): array
    {
        $allocations = $this->repository->getPaginatedAllocationsForNode(
            $request->getModel(Node::class)->id, 50
        );

        return $this->fractal->collection($allocations)
            ->transformWith($this->getTransformer(AllocationTransformer::class))
            ->toArray();
    }

    /**
     * Store new allocations for a given node.
     *
     * @param \Amghost\Http\Requests\Api\Application\Allocations\StoreAllocationRequest $request
     * @return \Illuminate\Http\Response
     *
     * @throws \Amghost\Exceptions\Service\Allocation\CidrOutOfRangeException
     * @throws \Amghost\Exceptions\Service\Allocation\InvalidPortMappingException
     * @throws \Amghost\Exceptions\Service\Allocation\PortOutOfRangeException
     * @throws \Amghost\Exceptions\Service\Allocation\TooManyPortsInRangeException
     */
    public function store(StoreAllocationRequest $request): Response
    {
        $this->assignmentService->handle($request->getModel(Node::class), $request->validated());

        return response('', 204);
    }

    /**
     * Delete a specific allocation from the Panel.
     *
     * @param \Amghost\Http\Requests\Api\Application\Allocations\DeleteAllocationRequest $request
     * @return \Illuminate\Http\Response
     *
     * @throws \Amghost\Exceptions\Service\Allocation\ServerUsingAllocationException
     */
    public function delete(DeleteAllocationRequest $request): Response
    {
        $this->deletionService->handle($request->getModel(Allocation::class));

        return response('', 204);
    }
}
