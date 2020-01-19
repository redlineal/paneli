<?php
/**
 * AMG HOST  -  PANEL
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Tests\Unit\Commands\Location;

use Mockery as m;
use Amghost\Models\Location;
use Tests\Unit\Commands\CommandTestCase;
use Amghost\Services\Locations\LocationDeletionService;
use Amghost\Console\Commands\Location\DeleteLocationCommand;
use Amghost\Contracts\Repository\LocationRepositoryInterface;

class DeleteLocationCommandTest extends CommandTestCase
{
    /**
     * @var \Amghost\Console\Commands\Location\DeleteLocationCommand
     */
    protected $command;

    /**
     * @var \Amghost\Services\Locations\LocationDeletionService|\Mockery\Mock
     */
    protected $deletionService;

    /**
     * @var \Amghost\Contracts\Repository\LocationRepositoryInterface|\Mockery\Mock
     */
    protected $repository;

    /**
     * Setup tests.
     */
    public function setUp()
    {
        parent::setUp();

        $this->deletionService = m::mock(LocationDeletionService::class);
        $this->repository = m::mock(LocationRepositoryInterface::class);

        $this->command = new DeleteLocationCommand($this->deletionService, $this->repository);
        $this->command->setLaravel($this->app);
    }

    /**
     * Test that a location can be deleted.
     */
    public function testLocationIsDeleted()
    {
        $locations = collect([
            $location1 = factory(Location::class)->make(),
            $location2 = factory(Location::class)->make(),
        ]);

        $this->repository->shouldReceive('all')->withNoArgs()->once()->andReturn($locations);
        $this->deletionService->shouldReceive('handle')->with($location2->id)->once()->andReturnNull();

        $display = $this->runCommand($this->command, [], [$location2->short]);

        $this->assertNotEmpty($display);
        $this->assertContains(trans('command/messages.location.deleted'), $display);
    }

    /**
     * Test that a location is deleted if passed in as an option.
     */
    public function testLocationIsDeletedIfPassedInOption()
    {
        $locations = collect([
            $location1 = factory(Location::class)->make(),
            $location2 = factory(Location::class)->make(),
        ]);

        $this->repository->shouldReceive('all')->withNoArgs()->once()->andReturn($locations);
        $this->deletionService->shouldReceive('handle')->with($location2->id)->once()->andReturnNull();

        $display = $this->withoutInteraction()->runCommand($this->command, [
            '--short' => $location2->short,
        ]);

        $this->assertNotEmpty($display);
        $this->assertContains(trans('command/messages.location.deleted'), $display);
    }

    /**
     * Test that prompt shows back up if the user enters the wrong parameters.
     */
    public function testInteractiveEnvironmentAllowsReAttemptingSearch()
    {
        $locations = collect([
            $location1 = factory(Location::class)->make(),
            $location2 = factory(Location::class)->make(),
        ]);

        $this->repository->shouldReceive('all')->withNoArgs()->once()->andReturn($locations);
        $this->deletionService->shouldReceive('handle')->with($location2->id)->once()->andReturnNull();

        $display = $this->runCommand($this->command, [], ['123_not_exist', 'another_not_exist', $location2->short]);

        $this->assertNotEmpty($display);
        $this->assertContains(trans('command/messages.location.no_location_found'), $display);
        $this->assertContains(trans('command/messages.location.deleted'), $display);
    }

    /**
     * Test that no re-attempt is performed in a non-interactive environment.
     */
    public function testNonInteractiveEnvironmentThrowsErrorIfNoLocationIsFound()
    {
        $locations = collect([
            $location1 = factory(Location::class)->make(),
            $location2 = factory(Location::class)->make(),
        ]);

        $this->repository->shouldReceive('all')->withNoArgs()->once()->andReturn($locations);
        $this->deletionService->shouldNotReceive('handle');

        $display = $this->withoutInteraction()->runCommand($this->command, ['--short' => 'randomTestString']);

        $this->assertNotEmpty($display);
        $this->assertContains(trans('command/messages.location.no_location_found'), $display);
    }
}
