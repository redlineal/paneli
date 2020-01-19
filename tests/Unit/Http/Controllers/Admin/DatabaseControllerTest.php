<?php
/**
 * AmgHost - Panel
 * Copyright (c) 2020 <lirimzm@yahoo.com>.
 */

namespace Tests\Unit\Http\Controllers\Admin;

use Mockery as m;
use Tests\TestCase;
use Amghost\Models\DatabaseHost;
use Prologue\Alerts\AlertsMessageBag;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\Assertions\ControllerAssertionsTrait;
use Amghost\Http\Controllers\Admin\DatabaseController;
use Amghost\Services\Databases\Hosts\HostUpdateService;
use Amghost\Services\Databases\Hosts\HostCreationService;
use Amghost\Services\Databases\Hosts\HostDeletionService;
use Amghost\Contracts\Repository\DatabaseRepositoryInterface;
use Amghost\Contracts\Repository\LocationRepositoryInterface;
use Amghost\Contracts\Repository\DatabaseHostRepositoryInterface;

class DatabaseControllerTest extends TestCase
{
    use ControllerAssertionsTrait;

    /**
     * @var \Prologue\Alerts\AlertsMessageBag|\Mockery\Mock
     */
    private $alert;

    /**
     * @var \Amghost\Services\Databases\Hosts\HostCreationService|\Mockery\Mock
     */
    private $creationService;

    /**
     * @var \Amghost\Contracts\Repository\DatabaseRepositoryInterface|\Mockery\Mock
     */
    private $databaseRepository;

    /**
     * @var \Amghost\Services\Databases\Hosts\HostDeletionService|\Mockery\Mock
     */
    private $deletionService;

    /**
     * @var \Amghost\Contracts\Repository\LocationRepositoryInterface|\Mockery\Mock
     */
    private $locationRepository;

    /**
     * @var \Amghost\Contracts\Repository\DatabaseHostRepositoryInterface|\Mockery\Mock
     */
    private $repository;

    /**
     * @var \Amghost\Services\Databases\Hosts\HostUpdateService|\Mockery\Mock
     */
    private $updateService;

    /**
     * Setup tests.
     */
    public function setUp()
    {
        parent::setUp();

        $this->alert = m::mock(AlertsMessageBag::class);
        $this->creationService = m::mock(HostCreationService::class);
        $this->databaseRepository = m::mock(DatabaseRepositoryInterface::class);
        $this->deletionService = m::mock(HostDeletionService::class);
        $this->locationRepository = m::mock(LocationRepositoryInterface::class);
        $this->repository = m::mock(DatabaseHostRepositoryInterface::class);
        $this->updateService = m::mock(HostUpdateService::class);
    }

    /**
     * Test the index controller.
     */
    public function testIndexController()
    {
        $this->locationRepository->shouldReceive('getAllWithNodes')->withNoArgs()->once()->andReturn(collect(['getAllWithNodes']));
        $this->repository->shouldReceive('getWithViewDetails')->withNoArgs()->once()->andReturn(collect(['getWithViewDetails']));

        $response = $this->getController()->index();

        $this->assertIsViewResponse($response);
        $this->assertViewNameEquals('admin.databases.index', $response);
        $this->assertViewHasKey('locations', $response);
        $this->assertViewHasKey('hosts', $response);
        $this->assertViewKeyEquals('locations', collect(['getAllWithNodes']), $response);
        $this->assertViewKeyEquals('hosts', collect(['getWithViewDetails']), $response);
    }

    /**
     * Test the view controller for displaying a specific database host.
     */
    public function testViewController()
    {
        $model = factory(DatabaseHost::class)->make();
        $paginator = new LengthAwarePaginator([], 1, 1);

        $this->locationRepository->shouldReceive('getAllWithNodes')->withNoArgs()->once()->andReturn(collect(['getAllWithNodes']));
        $this->repository->shouldReceive('find')->with(1)->once()->andReturn($model);
        $this->databaseRepository->shouldReceive('getDatabasesForHost')
            ->once()
            ->with(1)
            ->andReturn($paginator);

        $response = $this->getController()->view(1);

        $this->assertIsViewResponse($response);
        $this->assertViewNameEquals('admin.databases.view', $response);
        $this->assertViewHasKey('locations', $response);
        $this->assertViewHasKey('host', $response);
        $this->assertViewHasKey('databases', $response);
        $this->assertViewKeyEquals('locations', collect(['getAllWithNodes']), $response);
        $this->assertViewKeyEquals('host', $model, $response);
        $this->assertViewKeyEquals('databases', $paginator, $response);
    }

    /**
     * Return an instance of the DatabaseController with mock dependencies.
     *
     * @return \Amghost\Http\Controllers\Admin\DatabaseController
     */
    private function getController(): DatabaseController
    {
        return new DatabaseController(
            $this->alert,
            $this->repository,
            $this->databaseRepository,
            $this->creationService,
            $this->deletionService,
            $this->updateService,
            $this->locationRepository
        );
    }
}
