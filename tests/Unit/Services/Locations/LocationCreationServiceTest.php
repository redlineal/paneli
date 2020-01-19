<?php
/**
 * AmgHost - Panel
 * Copyright (c) 2020 <lirimzm@yahoo.com>.
 */

namespace Tests\Unit\Services\Locations;

use Mockery as m;
use Tests\TestCase;
use Amghost\Models\Location;
use Amghost\Services\Locations\LocationCreationService;
use Amghost\Contracts\Repository\LocationRepositoryInterface;

class LocationCreationServiceTest extends TestCase
{
    /**
     * @var \Amghost\Contracts\Repository\LocationRepositoryInterface
     */
    protected $repository;

    /**
     * @var \Amghost\Services\Locations\LocationCreationService
     */
    protected $service;

    /**
     * Setup tests.
     */
    public function setUp()
    {
        parent::setUp();

        $this->repository = m::mock(LocationRepositoryInterface::class);

        $this->service = new LocationCreationService($this->repository);
    }

    /**
     * Test that a location is created.
     */
    public function testLocationIsCreated()
    {
        $location = factory(Location::class)->make();

        $this->repository->shouldReceive('create')->with(['test_data' => 'test_value'])->once()->andReturn($location);

        $response = $this->service->handle(['test_data' => 'test_value']);
        $this->assertNotEmpty($response);
        $this->assertInstanceOf(Location::class, $response);
        $this->assertEquals($location, $response);
    }
}
