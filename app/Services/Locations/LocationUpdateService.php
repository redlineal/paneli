<?php
/**
 * AMGHOST - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Services\Locations;

use Amghost\Models\Location;
use Amghost\Contracts\Repository\LocationRepositoryInterface;

class LocationUpdateService
{
    /**
     * @var \Amghost\Contracts\Repository\LocationRepositoryInterface
     */
    protected $repository;

    /**
     * LocationUpdateService constructor.
     *
     * @param \Amghost\Contracts\Repository\LocationRepositoryInterface $repository
     */
    public function __construct(LocationRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Update an existing location.
     *
     * @param int|\Amghost\Models\Location $location
     * @param array                            $data
     * @return \Amghost\Models\Location
     *
     * @throws \Amghost\Exceptions\Model\DataValidationException
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function handle($location, array $data)
    {
        $location = ($location instanceof Location) ? $location->id : $location;

        return $this->repository->update($location, $data);
    }
}
