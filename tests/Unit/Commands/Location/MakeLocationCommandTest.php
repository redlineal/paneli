<?php
/**
 * AMG HOST  -  PANEL
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Tests\Unit\Commands\Location;

use Mockery as m;
use Amghost\Models\Location;
use Tests\Unit\Commands\CommandTestCase;
use Amghost\Services\Locations\LocationCreationService;
use Amghost\Console\Commands\Location\MakeLocationCommand;

class MakeLocationCommandTest extends CommandTestCase
{
    /**
     * @var \Amghost\Console\Commands\Location\MakeLocationCommand
     */
    protected $command;

    /**
     * @var \Amghost\Services\Locations\LocationCreationService|\Mockery\Mock
     */
    protected $creationService;

    /**
     * Setup tests.
     */
    public function setUp()
    {
        parent::setUp();

        $this->creationService = m::mock(LocationCreationService::class);

        $this->command = new MakeLocationCommand($this->creationService);
        $this->command->setLaravel($this->app);
    }

    /**
     * Test that a location can be created when no options are passed.
     */
    public function testLocationIsCreatedWithNoOptionsPassed()
    {
        $location = factory(Location::class)->make();

        $this->creationService->shouldReceive('handle')->with([
            'short' => $location->short,
            'long' => $location->long,
        ])->once()->andReturn($location);

        $display = $this->runCommand($this->command, [], [$location->short, $location->long]);

        $this->assertNotEmpty($display);
        $this->assertContains(trans('command/messages.location.created', [
            'name' => $location->short,
            'id' => $location->id,
        ]), $display);
    }

    /**
     * Test that a location is created when options are passed.
     */
    public function testLocationIsCreatedWhenOptionsArePassed()
    {
        $location = factory(Location::class)->make();

        $this->creationService->shouldReceive('handle')->with([
            'short' => $location->short,
            'long' => $location->long,
        ])->once()->andReturn($location);

        $display = $this->withoutInteraction()->runCommand($this->command, [
            '--short' => $location->short,
            '--long' => $location->long,
        ]);

        $this->assertNotEmpty($display);
        $this->assertContains(trans('command/messages.location.created', [
            'name' => $location->short,
            'id' => $location->id,
        ]), $display);
    }
}
