<?php
/**
 * Amghost - Panel
 * Copyright (c) 2020  Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Tests\Unit\Services\Servers;

use Exception;
use Mockery as m;
use Tests\TestCase;
use GuzzleHttp\Psr7\Response;
use Amghost\Models\Server;
use Psr\Log\LoggerInterface as Writer;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Database\ConnectionInterface;
use Amghost\Exceptions\DisplayException;
use Amghost\Services\Servers\SuspensionService;
use Amghost\Contracts\Repository\ServerRepositoryInterface;
use Amghost\Contracts\Repository\Daemon\ServerRepositoryInterface as DaemonServerRepositoryInterface;

class SuspensionServiceTest extends TestCase
{
    /**
     * @var \Amghost\Contracts\Repository\Daemon\ServerRepositoryInterface
     */
    protected $daemonServerRepository;

    /**
     * @var \Illuminate\Database\ConnectionInterface
     */
    protected $database;

    /**
     * @var \GuzzleHttp\Exception\RequestException
     */
    protected $exception;

    /**
     * @var \Amghost\Contracts\Repository\ServerRepositoryInterface
     */
    protected $repository;

    /**
     * @var \Amghost\Models\Server
     */
    protected $server;

    /**
     * @var \Amghost\Services\Servers\SuspensionService
     */
    protected $service;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $writer;

    /**
     * Setup tests.
     */
    public function setUp()
    {
        parent::setUp();

        $this->daemonServerRepository = m::mock(DaemonServerRepositoryInterface::class);
        $this->database = m::mock(ConnectionInterface::class);
        $this->exception = m::mock(RequestException::class)->makePartial();
        $this->repository = m::mock(ServerRepositoryInterface::class);
        $this->writer = m::mock(Writer::class);

        $this->server = factory(Server::class)->make(['suspended' => 0, 'node_id' => 1]);

        $this->service = new SuspensionService(
            $this->database,
            $this->daemonServerRepository,
            $this->repository,
            $this->writer
        );
    }

    /**
     * Test that the function accepts an integer in place of the server model.
     *
     * @expectedException \Exception
     */
    public function testFunctionShouldAcceptAnIntegerInPlaceOfAServerModel()
    {
        $this->repository->shouldReceive('find')->with($this->server->id)->once()->andThrow(new Exception());

        $this->service->toggle($this->server->id);
    }

    /**
     * Test that no action being passed suspends a server.
     */
    public function testServerShouldBeSuspendedWhenNoActionIsPassed()
    {
        $this->server->suspended = 0;

        $this->database->shouldReceive('beginTransaction')->withNoArgs()->once()->andReturnNull();
        $this->repository->shouldReceive('withoutFreshModel')->withNoArgs()->once()->andReturnSelf()
            ->shouldReceive('update')->with($this->server->id, ['suspended' => true])->once()->andReturnNull();

        $this->daemonServerRepository->shouldReceive('setServer')->with($this->server)->once()->andReturnSelf()
            ->shouldReceive('suspend')->withNoArgs()->once()->andReturn(new Response);
        $this->database->shouldReceive('commit')->withNoArgs()->once()->andReturnNull();

        $this->assertTrue($this->service->toggle($this->server));
    }

    /**
     * Test that server is unsuspended if action=unsuspend.
     */
    public function testServerShouldBeUnsuspendedWhenUnsuspendActionIsPassed()
    {
        $this->server->suspended = 1;

        $this->database->shouldReceive('beginTransaction')->withNoArgs()->once()->andReturnNull();
        $this->repository->shouldReceive('withoutFreshModel')->withNoArgs()->once()->andReturnSelf()
            ->shouldReceive('update')->with($this->server->id, ['suspended' => false])->once()->andReturnNull();

        $this->daemonServerRepository->shouldReceive('setServer')->with($this->server)->once()->andReturnSelf()
            ->shouldReceive('unsuspend')->withNoArgs()->once()->andReturn(new Response);
        $this->database->shouldReceive('commit')->withNoArgs()->once()->andReturnNull();

        $this->assertTrue($this->service->toggle($this->server, 'unsuspend'));
    }

    /**
     * Test that nothing happens if a server is already unsuspended and action=unsuspend.
     */
    public function testNoActionShouldHappenIfServerIsAlreadyUnsuspendedAndActionIsUnsuspend()
    {
        $this->server->suspended = 0;

        $this->assertTrue($this->service->toggle($this->server, 'unsuspend'));
    }

    /**
     * Test that nothing happens if a server is already suspended and action=suspend.
     */
    public function testNoActionShouldHappenIfServerIsAlreadySuspendedAndActionIsSuspend()
    {
        $this->server->suspended = 1;

        $this->assertTrue($this->service->toggle($this->server, 'suspend'));
    }

    /**
     * Test that an exception thrown by Guzzle is caught and transformed to a displayable exception.
     */
    public function testExceptionThrownByGuzzleShouldBeCaughtAndTransformedToDisplayable()
    {
        $this->server->suspended = 0;

        $this->database->shouldReceive('beginTransaction')->withNoArgs()->once()->andReturnNull();
        $this->repository->shouldReceive('withoutFreshModel')->withNoArgs()->once()->andReturnSelf()
            ->shouldReceive('update')->with($this->server->id, ['suspended' => true])->once()->andReturnNull();

        $this->daemonServerRepository->shouldReceive('setServer')->with($this->server)
            ->once()->andThrow($this->exception);

        $this->exception->shouldReceive('getResponse')->withNoArgs()->once()->andReturnSelf()
            ->shouldReceive('getStatusCode')->withNoArgs()->once()->andReturn(400);

        $this->writer->shouldReceive('warning')->with($this->exception)->once()->andReturnNull();

        try {
            $this->service->toggle($this->server);
        } catch (Exception $exception) {
            $this->assertInstanceOf(DisplayException::class, $exception);
            $this->assertEquals(
                trans('admin/server.exceptions.daemon_exception', ['code' => 400]),
                $exception->getMessage()
            );
        }
    }

    /**
     * Test that if action is not suspend or unsuspend an exception is thrown.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testExceptionShouldBeThrownIfActionIsNotValid()
    {
        $this->service->toggle($this->server, 'random');
    }
}
