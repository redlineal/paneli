<?php
/**
 * AmgHost - Panel
 * Copyright (c) 2020 <lirimzm@yahoo.com>.
 */

namespace Tests\Unit\Http\Controllers\Server;

use Mockery as m;
use Amghost\Models\Server;
use Amghost\Models\Subuser;
use Amghost\Models\Permission;
use Prologue\Alerts\AlertsMessageBag;
use Tests\Unit\Http\Controllers\ControllerTestCase;
use Amghost\Services\Subusers\SubuserUpdateService;
use Amghost\Services\Subusers\SubuserCreationService;
use Amghost\Services\Subusers\SubuserDeletionService;
use Amghost\Http\Controllers\Server\SubuserController;
use Amghost\Contracts\Repository\SubuserRepositoryInterface;
use Amghost\Http\Requests\Server\Subuser\SubuserStoreFormRequest;
use Amghost\Http\Requests\Server\Subuser\SubuserUpdateFormRequest;

class SubuserControllerTest extends ControllerTestCase
{
    /**
     * @var \Prologue\Alerts\AlertsMessageBag|\Mockery\Mock
     */
    protected $alert;

    /**
     * @var \Amghost\Contracts\Repository\SubuserRepositoryInterface|\Mockery\Mock
     */
    protected $repository;

    /**
     * @var \Amghost\Services\Subusers\SubuserCreationService|\Mockery\Mock
     */
    protected $subuserCreationService;

    /**
     * @var \Amghost\Services\Subusers\SubuserDeletionService|\Mockery\Mock
     */
    protected $subuserDeletionService;

    /**
     * @var \Amghost\Services\Subusers\SubuserUpdateService|\Mockery\Mock
     */
    protected $subuserUpdateService;

    /**
     * Setup tests.
     */
    public function setUp()
    {
        parent::setUp();

        $this->alert = m::mock(AlertsMessageBag::class);
        $this->repository = m::mock(SubuserRepositoryInterface::class);
        $this->subuserCreationService = m::mock(SubuserCreationService::class);
        $this->subuserDeletionService = m::mock(SubuserDeletionService::class);
        $this->subuserUpdateService = m::mock(SubuserUpdateService::class);
    }

    /*
     * Test index controller.
     */
    public function testIndexController()
    {
        $controller = $this->getController();
        $server = factory(Server::class)->make();

        $this->setRequestAttribute('server', $server);
        $this->mockInjectJavascript();

        $controller->shouldReceive('authorize')->with('list-subusers', $server)->once()->andReturnNull();
        $this->repository->shouldReceive('findWhere')->with([['server_id', '=', $server->id]])->once()->andReturn(collect());

        $response = $controller->index($this->request);
        $this->assertIsViewResponse($response);
        $this->assertViewNameEquals('server.users.index', $response);
        $this->assertViewHasKey('subusers', $response);
    }

    /**
     * Test view controller.
     */
    public function testViewController()
    {
        $controller = $this->getController();
        $subuser = factory(Subuser::class)->make();
        $subuser->setRelation('permissions', collect([
            (object) ['permission' => 'some.permission'],
            (object) ['permission' => 'another.permission'],
        ]));
        $server = factory(Server::class)->make();

        $this->setRequestAttribute('server', $server);
        $this->setRequestAttribute('subuser', $subuser);
        $this->mockInjectJavascript();

        $controller->shouldReceive('authorize')->with('view-subuser', $server)->once()->andReturnNull();
        $this->repository->shouldReceive('getWithPermissions')->with($subuser)->once()->andReturn($subuser);

        $response = $controller->view($this->request);
        $this->assertIsViewResponse($response);
        $this->assertViewNameEquals('server.users.view', $response);
        $this->assertViewHasKey('subuser', $response);
        $this->assertViewHasKey('permlist', $response);
        $this->assertViewHasKey('permissions', $response);
        $this->assertViewKeyEquals('subuser', $subuser, $response);
        $this->assertViewKeyEquals('permlist', Permission::getPermissions(), $response);
        $this->assertViewKeyEquals('permissions', collect([
            'some.permission' => true,
            'another.permission' => true,
        ]), $response);
    }

    /**
     * Test the update controller.
     */
    public function testUpdateController()
    {
        $this->setRequestMockClass(SubuserUpdateFormRequest::class);

        $controller = $this->getController();
        $subuser = factory(Subuser::class)->make();

        $this->setRequestAttribute('subuser', $subuser);

        $this->request->shouldReceive('input')->with('permissions', [])->once()->andReturn(['some.permission']);
        $this->subuserUpdateService->shouldReceive('handle')->with($subuser, ['some.permission'])->once()->andReturnNull();
        $this->alert->shouldReceive('success')->with(trans('server.users.user_updated'))->once()->andReturnSelf();
        $this->alert->shouldReceive('flash')->withNoArgs()->once()->andReturnNull();

        $response = $controller->update($this->request, 'abcd1234', $subuser->hashid);
        $this->assertIsRedirectResponse($response);
        $this->assertRedirectRouteEquals('server.subusers.view', $response, ['uuid' => 'abcd1234', 'id' => $subuser->hashid]);
    }

    /**
     * Test the create controller.
     */
    public function testCreateController()
    {
        $controller = $this->getController();
        $server = factory(Server::class)->make();

        $this->setRequestAttribute('server', $server);
        $this->mockInjectJavascript();

        $controller->shouldReceive('authorize')->with('create-subuser', $server)->once()->andReturnNull();

        $response = $controller->create($this->request);
        $this->assertIsViewResponse($response);
        $this->assertViewNameEquals('server.users.new', $response);
        $this->assertViewHasKey('permissions', $response);
        $this->assertViewKeyEquals('permissions', Permission::getPermissions(), $response);
    }

    /**
     * Test the store controller.
     */
    public function testStoreController()
    {
        $this->setRequestMockClass(SubuserStoreFormRequest::class);
        $controller = $this->getController();

        $server = factory(Server::class)->make();
        $subuser = factory(Subuser::class)->make();

        $this->setRequestAttribute('server', $server);

        $this->request->shouldReceive('input')->with('email')->once()->andReturn('user@test.com');
        $this->request->shouldReceive('input')->with('permissions', [])->once()->andReturn(['some.permission']);
        $this->subuserCreationService->shouldReceive('handle')->with($server, 'user@test.com', ['some.permission'])->once()->andReturn($subuser);
        $this->alert->shouldReceive('success')->with(trans('server.users.user_assigned'))->once()->andReturnSelf();
        $this->alert->shouldReceive('flash')->withNoArgs()->once()->andReturnNull();

        $response = $controller->store($this->request);
        $this->assertIsRedirectResponse($response);
        $this->assertRedirectRouteEquals('server.subusers.view', $response, [
            'uuid' => $server->uuidShort,
            'id' => $subuser->hashid,
        ]);
    }

    /**
     * Test the delete controller.
     */
    public function testDeleteController()
    {
        $controller = $this->getController();

        $server = factory(Server::class)->make();
        $subuser = factory(Subuser::class)->make();

        $this->setRequestAttribute('server', $server);
        $this->setRequestAttribute('subuser', $subuser);

        $controller->shouldReceive('authorize')->with('delete-subuser', $server)->once()->andReturnNull();
        $this->subuserDeletionService->shouldReceive('handle')->with($subuser)->once()->andReturnNull();

        $response = $controller->delete($this->request);
        $this->assertIsResponse($response);
        $this->assertResponseCodeEquals(204, $response);
    }

    /**
     * Return a mocked instance of the controller to allow access to authorization functionality.
     *
     * @return \Amghost\Http\Controllers\Server\SubuserController|\Mockery\Mock
     */
    private function getController()
    {
        return $this->buildMockedController(SubuserController::class, [
            $this->alert,
            $this->subuserCreationService,
            $this->subuserDeletionService,
            $this->repository,
            $this->subuserUpdateService,
        ]);
    }
}
