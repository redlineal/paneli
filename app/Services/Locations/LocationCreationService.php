<?php
/**
 * AMGHOST - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Services\Locations;

use Amghost\Contracts\Repository\LocationRepositoryInterface;

class LocationCreationService
{
    /**
     * @var \Amghost\Contracts\Repository\LocationRepositoryInterface
     */
    protected $repository;

    /**
     * LocationCreationService constructor.
     *
     * @param \Amghost\Contracts\Repository\LocationRepositoryInterface $repository
     */
    public function __construct(LocationRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Create a new location.
     *
     * @param array $data
     * @return \Amghost\Models\Location
     *
     * @throws \Amghost\Exceptions\Model\DataValidationException
     */
    public function handle(array $data)
    {
        return $this->repository->create($data);
    }
}
