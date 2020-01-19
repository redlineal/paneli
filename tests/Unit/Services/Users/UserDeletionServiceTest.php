<?php
/**
 * Amghost - Panel
 * Copyright (c) 2020  Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Tests\Unit\Services\Users;

use Mockery as m;
use Tests\TestCase;
use Amghost\Models\User;
use Illuminate\Contracts\Translation\Translator;
use Amghost\Services\Users\UserDeletionService;
use Amghost\Contracts\Repository\UserRepositoryInterface;
use Amghost\Contracts\Repository\ServerRepositoryInterface;

class UserDeletionServiceTest extends TestCase
{
    /**
     * @var \Amghost\Contracts\Repository\UserRepositoryInterface
     */
    protected $repository;

    /**
     * @var \Illuminate\Contracts\Translation\Translator
     */
    protected $translator;

    /**
     * @var \Amghost\Contracts\Repository\ServerRepositoryInterface
     */
    protected $serverRepository;

    /**
     * @var \Amghost\Services\Users\UserDeletionService
     */
    protected $service;

    /**
     * @var User
     */
    protected $user;

    /**
     * Setup tests.
     */
    public function setUp()
    {
        parent::setUp();

        $this->user = factory(User::class)->make();
        $this->repository = m::mock(UserRepositoryInterface::class);
        $this->translator = m::mock(Translator::class);
        $this->serverRepository = m::mock(ServerRepositoryInterface::class);

        $this->service = new UserDeletionService(
            $this->serverRepository,
            $this->translator,
            $this->repository
        );
    }

    /**
     * Test that a user is deleted if they have no servers.
     */
    public function testUserIsDeletedIfNoServersAreAttachedToAccount()
    {
        $this->serverRepository->shouldReceive('setColumns')->with('id')->once()->andReturnSelf()
            ->shouldReceive('findCountWhere')->with([['owner_id', '=', $this->user->id]])->once()->andReturn(0);
        $this->repository->shouldReceive('delete')->with($this->user->id)->once()->andReturn(1);

        $this->assertEquals(1, $this->service->handle($this->user->id));
    }

    /**
     * Test that an exception is thrown if trying to delete a user with servers.
     *
     * @expectedException \Amghost\Exceptions\DisplayException
     */
    public function testExceptionIsThrownIfServersAreAttachedToAccount()
    {
        $this->serverRepository->shouldReceive('setColumns')->with('id')->once()->andReturnSelf()
            ->shouldReceive('findCountWhere')->with([['owner_id', '=', $this->user->id]])->once()->andReturn(1);
        $this->translator->shouldReceive('trans')->with('admin/user.exceptions.user_has_servers')->once()->andReturnNull();

        $this->service->handle($this->user->id);
    }

    /**
     * Test that the function supports passing in a model or an ID.
     */
    public function testModelCanBePassedInPlaceOfUserId()
    {
        $this->serverRepository->shouldReceive('setColumns')->with('id')->once()->andReturnSelf()
            ->shouldReceive('findCountWhere')->with([['owner_id', '=', $this->user->id]])->once()->andReturn(0);
        $this->repository->shouldReceive('delete')->with($this->user->id)->once()->andReturn(1);

        $this->assertEquals(1, $this->service->handle($this->user));
    }
}
