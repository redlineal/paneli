<?php
/**
 * AmgHost - Panel
 * Copyright (c) 2020 <lirimzm@yahoo.com>.
 */

namespace Tests\Unit\Services\Services;

use Mockery as m;
use Tests\TestCase;
use Amghost\Models\Nest;
use Tests\Traits\MocksUuids;
use Illuminate\Contracts\Config\Repository;
use Amghost\Services\Nests\NestCreationService;
use Amghost\Contracts\Repository\NestRepositoryInterface;

class NestCreationServiceTest extends TestCase
{
    use MocksUuids;

    /**
     * @var \Illuminate\Contracts\Config\Repository|\Mockery\Mock
     */
    private $config;

    /**
     * @var \Amghost\Contracts\Repository\NestRepositoryInterface|\Mockery\Mock
     */
    private $repository;

    /**
     * Setup tests.
     */
    public function setUp()
    {
        parent::setUp();

        $this->config = m::mock(Repository::class);
        $this->repository = m::mock(NestRepositoryInterface::class);
    }

    /**
     * Test that a new service can be created using the correct data.
     */
    public function testCreateNewService()
    {
        $model = factory(Nest::class)->make();

        $this->config->shouldReceive('get')->with('amghost.service.author')->once()->andReturn('testauthor@example.com');
        $this->repository->shouldReceive('create')->with([
            'uuid' => $this->getKnownUuid(),
            'author' => 'testauthor@example.com',
            'name' => $model->name,
            'description' => $model->description,
        ], true, true)->once()->andReturn($model);

        $response = $this->getService()->handle(['name' => $model->name, 'description' => $model->description]);
        $this->assertInstanceOf(Nest::class, $response);
        $this->assertEquals($model, $response);
    }

    /**
     * Test creation of a new nest with a defined author. This is used by seeder
     * scripts which need to set a specific author for nests in order for other
     * functionality to work correctly.
     */
    public function testCreateServiceWithDefinedAuthor()
    {
        $model = factory(Nest::class)->make();

        $this->repository->shouldReceive('create')->with([
            'uuid' => $this->getKnownUuid(),
            'author' => 'support@amghost.io',
            'name' => $model->name,
            'description' => $model->description,
        ], true, true)->once()->andReturn($model);

        $response = $this->getService()->handle(['name' => $model->name, 'description' => $model->description], 'support@amghost.io');
        $this->assertInstanceOf(Nest::class, $response);
        $this->assertEquals($model, $response);
    }

    /**
     * Return an instance of the service with mocked dependencies.
     *
     * @return \Amghost\Services\Nests\NestCreationService
     */
    private function getService(): NestCreationService
    {
        return new NestCreationService($this->config, $this->repository);
    }
}
