<?php

namespace Tests\Unit\Http\Middleware;

use Mockery as m;
use Amghost\Models\Node;
use Illuminate\Http\Response;
use Amghost\Models\Server;
use Illuminate\Contracts\Routing\ResponseFactory;
use Amghost\Http\Middleware\MaintenanceMiddleware;

class MaintenanceMiddlewareTest extends MiddlewareTestCase
{
    /**
     * @var \Illuminate\Contracts\Routing\ResponseFactory|\Mockery\Mock
     */
    private $response;

    /**
     * Setup tests.
     */
    public function setUp()
    {
        parent::setUp();

        $this->response = m::mock(ResponseFactory::class);
    }

    /**
     * Test that a node not in maintenance mode continues through the request cycle.
     */
    public function testHandle()
    {
        $server = factory(Server::class)->make();
        $node = factory(Node::class)->make(['maintenance' => 0]);

        $server->setRelation('node', $node);
        $this->setRequestAttribute('server', $server);

        $this->getMiddleware()->handle($this->request, $this->getClosureAssertions());
    }

    /**
     * Test that a node in maintenance mode returns an error view.
     */
    public function testHandleInMaintenanceMode()
    {
        $server = factory(Server::class)->make();
        $node = factory(Node::class)->make(['maintenance_mode' => 1]);

        $server->setRelation('node', $node);
        $this->setRequestAttribute('server', $server);

        $this->response->shouldReceive('view')
            ->once()
            ->with('errors.maintenance')
            ->andReturn(new Response);

        $response = $this->getMiddleware()->handle($this->request, $this->getClosureAssertions());
        $this->assertInstanceOf(Response::class, $response);
    }

    /**
     * @return \Amghost\Http\Middleware\MaintenanceMiddleware
     */
    private function getMiddleware(): MaintenanceMiddleware
    {
        return new MaintenanceMiddleware($this->response);
    }
}