<?php
/**
 * Amghost - Panel
 * Copyright (c) 2020  Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Tests\Unit\Services\Nodes;

use Mockery as m;
use Tests\TestCase;
use phpmock\phpunit\PHPMock;
use Amghost\Services\Nodes\NodeCreationService;
use Amghost\Contracts\Repository\NodeRepositoryInterface;

class NodeCreationServiceTest extends TestCase
{
    use PHPMock;

    /**
     * @var \Amghost\Contracts\Repository\NodeRepositoryInterface
     */
    protected $repository;

    /**
     * @var \Amghost\Services\Nodes\NodeCreationService
     */
    protected $service;

    /**
     * Setup tests.
     */
    public function setUp()
    {
        parent::setUp();

        $this->repository = m::mock(NodeRepositoryInterface::class);

        $this->service = new NodeCreationService($this->repository);
    }

    /**
     * Test that a node is created and a daemon secret token is created.
     */
    public function testNodeIsCreatedAndDaemonSecretIsGenerated()
    {
        $this->getFunctionMock('\\Amghost\\Services\\Nodes', 'str_random')
            ->expects($this->once())->willReturn('random_string');

        $this->repository->shouldReceive('create')->with([
            'name' => 'NodeName',
            'daemonSecret' => 'random_string',
        ])->once()->andReturnNull();

        $this->assertNull($this->service->handle(['name' => 'NodeName']));
    }
}
