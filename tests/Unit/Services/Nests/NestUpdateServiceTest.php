<?php
/**
 * AmgHost - Panel
 * Copyright (c) 2020 <lirimzm@yahoo.com>.
 */

namespace Tests\Unit\Services\Services;

use Mockery as m;
use Tests\TestCase;
use Amghost\Services\Nests\NestUpdateService;
use Amghost\Contracts\Repository\NestRepositoryInterface;

class NestUpdateServiceTest extends TestCase
{
    /**
     * @var \Amghost\Contracts\Repository\NestRepositoryInterface|\Mockery\Mock
     */
    protected $repository;

    /**
     * @var \Amghost\Services\Nests\NestUpdateService
     */
    protected $service;

    /**
     * Setup tests.
     */
    public function setUp()
    {
        parent::setUp();

        $this->repository = m::mock(NestRepositoryInterface::class);

        $this->service = new NestUpdateService($this->repository);
    }

    /**
     * Test that the author key is removed from the data array before updating the record.
     */
    public function testAuthorArrayKeyIsRemovedIfPassed()
    {
        $this->repository->shouldReceive('withoutFreshModel')->withNoArgs()->once()->andReturnSelf()
            ->shouldReceive('update')->with(1, ['otherfield' => 'value'])->once()->andReturnNull();

        $this->service->handle(1, ['author' => 'author1', 'otherfield' => 'value']);
    }

    /**
     * Test that the function continues to work when no author key is passed.
     */
    public function testServiceIsUpdatedWhenNoAuthorKeyIsPassed()
    {
        $this->repository->shouldReceive('withoutFreshModel')->withNoArgs()->once()->andReturnSelf()
            ->shouldReceive('update')->with(1, ['otherfield' => 'value'])->once()->andReturnNull();

        $this->service->handle(1, ['otherfield' => 'value']);
    }
}
