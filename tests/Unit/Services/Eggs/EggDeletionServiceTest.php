<?php
/**
 * AmgHost - Panel
 * Copyright (c) 2020 <lirimzm@yahoo.com>.
 */

namespace Tests\Unit\Services\Services\Options;

use Mockery as m;
use Tests\TestCase;
use Amghost\Exceptions\AmghostException;
use Amghost\Services\Eggs\EggDeletionService;
use Amghost\Contracts\Repository\EggRepositoryInterface;
use Amghost\Exceptions\Service\Egg\HasChildrenException;
use Amghost\Exceptions\Service\HasActiveServersException;
use Amghost\Contracts\Repository\ServerRepositoryInterface;

class EggDeletionServiceTest extends TestCase
{
    /**
     * @var \Amghost\Contracts\Repository\EggRepositoryInterface|\Mockery\Mock
     */
    protected $repository;

    /**
     * @var \Amghost\Contracts\Repository\ServerRepositoryInterface|\Mockery\Mock
     */
    protected $serverRepository;

    /**
     * @var \Amghost\Services\Eggs\EggDeletionService
     */
    protected $service;

    /**
     * Setup tests.
     */
    public function setUp()
    {
        parent::setUp();

        $this->repository = m::mock(EggRepositoryInterface::class);
        $this->serverRepository = m::mock(ServerRepositoryInterface::class);

        $this->service = new EggDeletionService($this->serverRepository, $this->repository);
    }

    /**
     * Test that Egg is deleted if no servers are found.
     */
    public function testEggIsDeletedIfNoServersAreFound()
    {
        $this->serverRepository->shouldReceive('findCountWhere')->with([['egg_id', '=', 1]])->once()->andReturn(0);
        $this->repository->shouldReceive('findCountWhere')->with([['config_from', '=', 1]])->once()->andReturn(0);
        $this->repository->shouldReceive('delete')->with(1)->once()->andReturn(1);

        $this->assertEquals(1, $this->service->handle(1));
    }

    /**
     * Test that Egg is not deleted if servers are found.
     */
    public function testExceptionIsThrownIfServersAreFound()
    {
        $this->serverRepository->shouldReceive('findCountWhere')->with([['egg_id', '=', 1]])->once()->andReturn(1);

        try {
            $this->service->handle(1);
        } catch (AmghostException $exception) {
            $this->assertInstanceOf(HasActiveServersException::class, $exception);
            $this->assertEquals(trans('exceptions.nest.egg.delete_has_servers'), $exception->getMessage());
        }
    }

    /**
     * Test that an exception is thrown if children Eggs exist.
     */
    public function testExceptionIsThrownIfChildrenArePresent()
    {
        $this->serverRepository->shouldReceive('findCountWhere')->with([['egg_id', '=', 1]])->once()->andReturn(0);
        $this->repository->shouldReceive('findCountWhere')->with([['config_from', '=', 1]])->once()->andReturn(1);

        try {
            $this->service->handle(1);
        } catch (AmghostException $exception) {
            $this->assertInstanceOf(HasChildrenException::class, $exception);
            $this->assertEquals(trans('exceptions.nest.egg.has_children'), $exception->getMessage());
        }
    }
}
