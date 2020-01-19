<?php

namespace Amghost\Services\Allocations;

use Amghost\Models\Allocation;
use Amghost\Contracts\Repository\AllocationRepositoryInterface;
use Amghost\Exceptions\Service\Allocation\ServerUsingAllocationException;

class AllocationDeletionService
{
    /**
     * @var \Amghost\Contracts\Repository\AllocationRepositoryInterface
     */
    private $repository;

    /**
     * AllocationDeletionService constructor.
     *
     * @param \Amghost\Contracts\Repository\AllocationRepositoryInterface $repository
     */
    public function __construct(AllocationRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Delete an allocation from the database only if it does not have a server
     * that is actively attached to it.
     *
     * @param \Amghost\Models\Allocation $allocation
     * @return int
     *
     * @throws \Amghost\Exceptions\Service\Allocation\ServerUsingAllocationException
     */
    public function handle(Allocation $allocation)
    {
        if (! is_null($allocation->server_id)) {
            throw new ServerUsingAllocationException(trans('exceptions.allocations.server_using'));
        }

        return $this->repository->delete($allocation->id);
    }
}
