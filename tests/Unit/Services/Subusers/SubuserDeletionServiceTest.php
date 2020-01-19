<?php
/**
 * Amghost - Panel
 * Copyright (c) 2020  Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Tests\Unit\Services\Subusers;

use Mockery as m;
use Tests\TestCase;
use Amghost\Models\Subuser;
use Illuminate\Database\ConnectionInterface;
use Amghost\Services\Subusers\SubuserDeletionService;
use Amghost\Services\DaemonKeys\DaemonKeyDeletionService;
use Amghost\Contracts\Repository\SubuserRepositoryInterface;

class SubuserDeletionServiceTest extends TestCase
{
    /**
     * @var \Illuminate\Database\ConnectionInterface|\Mockery\Mock
     */
    private $connection;

    /**
     * @var \Amghost\Services\DaemonKeys\DaemonKeyDeletionService|\Mockery\Mock
     */
    private $keyDeletionService;

    /**
     * @var \Amghost\Contracts\Repository\SubuserRepositoryInterface|\Mockery\Mock
     */
    private $repository;

    /**
     * Setup tests.
     */
    public function setUp()
    {
        parent::setUp();

        $this->connection = m::mock(ConnectionInterface::class);
        $this->keyDeletionService = m::mock(DaemonKeyDeletionService::class);
        $this->repository = m::mock(SubuserRepositoryInterface::class);
    }

    /**
     * Test that a subuser is deleted correctly.
     */
    public function testSubuserIsDeleted()
    {
        $subuser = factory(Subuser::class)->make();

        $this->connection->shouldReceive('beginTransaction')->withNoArgs()->once()->andReturnNull();
        $this->keyDeletionService->shouldReceive('handle')->with($subuser->server_id, $subuser->user_id)->once()->andReturnNull();
        $this->repository->shouldReceive('delete')->with($subuser->id)->once()->andReturn(1);
        $this->connection->shouldReceive('commit')->withNoArgs()->once()->andReturnNull();

        $this->getService()->handle($subuser);
        $this->assertTrue(true);
    }

    /**
     * Return an instance of the service with mocked dependencies for testing.
     *
     * @return \Amghost\Services\Subusers\SubuserDeletionService
     */
    private function getService(): SubuserDeletionService
    {
        return new SubuserDeletionService($this->connection, $this->keyDeletionService, $this->repository);
    }
}
