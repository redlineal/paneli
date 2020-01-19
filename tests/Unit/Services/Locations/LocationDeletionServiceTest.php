<?php
/**
 * AmgHost - Panel
 * Copyright (c) 2020 <lirimzm@yahoo.com>.
 */

namespace Tests\Unit\Services\Locations;

use Mockery as m;
use Tests\TestCase;
use Amghost\Exceptions\DisplayException;
use Amghost\Services\Locations\LocationDeletionService;
use Amghost\Contracts\Repository\NodeRepositoryInterface;
use Amghost\Contracts\Repository\LocationRepositoryInterface;
use Amghost\Exceptions\Service\Location\HasActiveNodesException;

class LocationDeletionServiceTest extends TestCase
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
     * @var \Amghost\Services\Locations\LocationDeletionService
     */
    protected $service;

    /**
     * Setup tests.
     */
    public function setUp()
    {
        parent::setUp();

        $this->nodeRepository = m::mock(NodeRepositoryInterface::class);
        $this->repository = m::mock(LocationRepositoryInterface::class);

        $this->service = new LocationDeletionService($this->repository, $this->nodeRepository);
    }

    /**
     * Test that a location is deleted.
     */
    public function testLocationIsDeleted()
    {
        $this->nodeRepository->shouldReceive('findCountWhere')->with([['location_id', '=', 123]])->once()->andReturn(0);
        $this->repository->shouldReceive('delete')->with(123)->once()->andReturn(1);

        $response = $this->service->handle(123);
        $this->assertEquals(1, $response);
    }

    /**
     * Test that an exception is thrown if nodes are attached to a location.
     */
    public function testExceptionIsThrownIfNodesAreAttached()
    {
        $this->nodeRepository->shouldReceive('findCountWhere')->with([['location_id', '=', 123]])->once()->andReturn(1);

        try {
            $this->service->handle(123);
        } catch (DisplayException $exception) {
            $this->assertInstanceOf(HasActiveNodesException::class, $exception);
            $this->assertEquals(trans('exceptions.locations.has_nodes'), $exception->getMessage());
        }
    }
}
