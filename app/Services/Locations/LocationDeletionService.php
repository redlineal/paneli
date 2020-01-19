<?php
/**
 * AMGHOST - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Services\Locations;

use Webmozart\Assert\Assert;
use Amghost\Models\Location;
use Amghost\Contracts\Repository\NodeRepositoryInterface;
use Amghost\Contracts\Repository\LocationRepositoryInterface;
use Amghost\Exceptions\Service\Location\HasActiveNodesException;

class LocationDeletionService
{
    /**
     * @var \Amghost\Contracts\Repository\NodeRepositoryInterface
     */
    protected $nodeRepository;

    /**
     * @var \Amghost\Contracts\Repository\LocationRepositoryInterface
     */
    protected $repository;

    /**
     * LocationDeletionService constructor.
     *
     * @param \Amghost\Contracts\Repository\LocationRepositoryInterface $repository
     * @param \Amghost\Contracts\Repository\NodeRepositoryInterface     $nodeRepository
     */
    public function __construct(
        LocationRepositoryInterface $repository,
        NodeRepositoryInterface $nodeRepository
    ) {
        $this->nodeRepository = $nodeRepository;
        $this->repository = $repository;
    }

    /**
     * Delete an existing location.
     *
     * @param int|\Amghost\Models\Location $location
     * @return int|null
     *
     * @throws \Amghost\Exceptions\Service\Location\HasActiveNodesException
     */
    public function handle($location)
    {
        $location = ($location instanceof Location) ? $location->id : $location;

        Assert::integerish($location, 'First argument passed to handle must be numeric or an instance of ' . Location::class . ', received %s.');

        $count = $this->nodeRepository->findCountWhere([['location_id', '=', $location]]);
        if ($count > 0) {
            throw new HasActiveNodesException(trans('exceptions.locations.has_nodes'));
        }

        return $this->repository->delete($location);
    }
}
