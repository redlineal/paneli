<?php
/**
 * Amghost - Panel
 * Copyright (c) 2020  Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Tests\Unit\Services\Subusers;

use Mockery as m;
use Tests\TestCase;
use Amghost\Services\Subusers\PermissionCreationService;
use Amghost\Contracts\Repository\PermissionRepositoryInterface;

class PermissionCreationServiceTest extends TestCase
{
    /**
     * @var \Amghost\Contracts\Repository\PermissionRepositoryInterface|\Mockery\Mock
     */
    protected $repository;

    /**
     * @var \Amghost\Services\Subusers\PermissionCreationService
     */
    protected $service;

    /**
     * Setup tests.
     */
    public function setUp()
    {
        parent::setUp();

        $this->repository = m::mock(PermissionRepositoryInterface::class);
        $this->service = new PermissionCreationService($this->repository);
    }

    /**
     * Test that permissions can be assigned correctly.
     */
    public function testPermissionsAreAssignedCorrectly()
    {
        $permissions = ['access-sftp'];

        $this->repository->shouldReceive('withoutFreshModel')->withNoArgs()->once()->andReturnSelf()
            ->shouldReceive('insert')->with([
                ['subuser_id' => 1, 'permission' => 'access-sftp'],
            ])->once()->andReturn(true);

        $this->service->handle(1, $permissions);
        $this->assertTrue(true);
    }
}
