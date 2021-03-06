<?php
/**
 * Amghost - Panel
 * Copyright (c) 2020  Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Tests\Unit\Services\Subusers;

use Mockery as m;
use Tests\TestCase;
use Amghost\Models\User;
use GuzzleHttp\Psr7\Response;
use Amghost\Models\Server;
use Amghost\Models\Subuser;
use Tests\Traits\MocksRequestException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Database\ConnectionInterface;
use Amghost\Exceptions\AmghostException;
use Amghost\Services\Subusers\SubuserUpdateService;
use Amghost\Services\Subusers\PermissionCreationService;
use Amghost\Services\DaemonKeys\DaemonKeyProviderService;
use Amghost\Contracts\Repository\SubuserRepositoryInterface;
use Amghost\Contracts\Repository\PermissionRepositoryInterface;
use Amghost\Exceptions\Http\Connection\DaemonConnectionException;
use Amghost\Contracts\Repository\Daemon\ServerRepositoryInterface as DaemonServerRepositoryInterface;

class SubuserUpdateServiceTest extends TestCase
{
    use MocksRequestException;

    /**
     * @var \Illuminate\Database\ConnectionInterface|\Mockery\Mock
     */
    private $connection;

    /**
     * @var \Amghost\Contracts\Repository\Daemon\ServerRepositoryInterface|\Mockery\Mock
     */
    private $daemonRepository;

    /**
     * @var \Amghost\Services\DaemonKeys\DaemonKeyProviderService|\Mockery\Mock
     */
    private $keyProviderService;

    /**
     * @var \Amghost\Contracts\Repository\PermissionRepositoryInterface|\Mockery\Mock
     */
    private $permissionRepository;

    /**
     * @var \Amghost\Services\Subusers\PermissionCreationService|\Mockery\Mock
     */
    private $permissionService;

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
        $this->daemonRepository = m::mock(DaemonServerRepositoryInterface::class);
        $this->keyProviderService = m::mock(DaemonKeyProviderService::class);
        $this->permissionRepository = m::mock(PermissionRepositoryInterface::class);
        $this->permissionService = m::mock(PermissionCreationService::class);
        $this->repository = m::mock(SubuserRepositoryInterface::class);
    }

    /**
     * Test that permissions are updated in the database.
     */
    public function testPermissionsAreUpdated()
    {
        $subuser = factory(Subuser::class)->make();
        $subuser->setRelation('server', factory(Server::class)->make());
        $subuser->setRelation('user', factory(User::class)->make());

        $this->repository->shouldReceive('loadServerAndUserRelations')->with($subuser)->once()->andReturn($subuser);
        $this->connection->shouldReceive('beginTransaction')->withNoArgs()->once()->andReturnNull();
        $this->permissionRepository->shouldReceive('deleteWhere')->with([['subuser_id', '=', $subuser->id]])->once()->andReturn(1);
        $this->permissionService->shouldReceive('handle')->with($subuser->id, ['some-permission'])->once()->andReturnNull();

        $this->keyProviderService->shouldReceive('handle')->with($subuser->server, $subuser->user, false)->once()->andReturn('test123');
        $this->daemonRepository->shouldReceive('setServer')->with($subuser->server)->once()->andReturnSelf();
        $this->daemonRepository->shouldReceive('revokeAccessKey')->with('test123')->once()->andReturn(new Response);

        $this->connection->shouldReceive('commit')->withNoArgs()->once()->andReturnNull();

        $this->getService()->handle($subuser, ['some-permission']);
        $this->assertTrue(true);
    }

    /**
     * Test that an exception is thrown if the daemon connection fails.
     */
    public function testExceptionIsThrownIfDaemonConnectionFails()
    {
        $this->configureExceptionMock();

        $subuser = factory(Subuser::class)->make();
        $subuser->setRelation('server', factory(Server::class)->make());
        $subuser->setRelation('user', factory(User::class)->make());

        $this->repository->shouldReceive('loadServerAndUserRelations')->with($subuser)->once()->andReturn($subuser);
        $this->connection->shouldReceive('beginTransaction')->withNoArgs()->once()->andReturnNull();
        $this->permissionRepository->shouldReceive('deleteWhere')->with([['subuser_id', '=', $subuser->id]])->once()->andReturn(1);
        $this->permissionService->shouldReceive('handle')->with($subuser->id, [])->once()->andReturnNull();

        $this->keyProviderService->shouldReceive('handle')->with($subuser->server, $subuser->user, false)->once()->andReturn('test123');
        $this->daemonRepository->shouldReceive('setServer')->with($subuser->server)->once()->andThrow($this->getExceptionMock());
        $this->connection->shouldReceive('rollBack')->withNoArgs()->once()->andReturnNull();

        try {
            $this->getService()->handle($subuser, []);
        } catch (AmghostException $exception) {
            $this->assertInstanceOf(DaemonConnectionException::class, $exception);
            $this->assertInstanceOf(RequestException::class, $exception->getPrevious());
        }
    }

    /**
     * Return an instance of the service with mocked dependencies for testing.
     *
     * @return \Amghost\Services\Subusers\SubuserUpdateService
     */
    private function getService(): SubuserUpdateService
    {
        return new SubuserUpdateService(
            $this->connection,
            $this->keyProviderService,
            $this->daemonRepository,
            $this->permissionService,
            $this->permissionRepository,
            $this->repository
        );
    }
}
