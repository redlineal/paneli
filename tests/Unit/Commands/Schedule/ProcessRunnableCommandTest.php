<?php
/**
 * AMG HOST  -  PANEL
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Tests\Unit\Commands\Schedule;

use Mockery as m;
use Cake\Chronos\Chronos;
use Amghost\Models\Task;
use Amghost\Models\Schedule;
use Tests\Unit\Commands\CommandTestCase;
use Amghost\Services\Schedules\ProcessScheduleService;
use Amghost\Console\Commands\Schedule\ProcessRunnableCommand;
use Amghost\Contracts\Repository\ScheduleRepositoryInterface;

class ProcessRunnableCommandTest extends CommandTestCase
{
    /**
     * @var \Amghost\Console\Commands\Schedule\ProcessRunnableCommand
     */
    protected $command;

    /**
     * @var \Amghost\Services\Schedules\ProcessScheduleService
     */
    protected $processScheduleService;

    /**
     * @var \Amghost\Contracts\Repository\ScheduleRepositoryInterface
     */
    protected $repository;

    /**
     * Setup tests.
     */
    public function setUp()
    {
        parent::setUp();

        Chronos::setTestNow(Chronos::now());

        $this->processScheduleService = m::mock(ProcessScheduleService::class);
        $this->repository = m::mock(ScheduleRepositoryInterface::class);

        $this->command = new ProcessRunnableCommand($this->processScheduleService, $this->repository);
    }

    /**
     * Test that a schedule can be queued up correctly.
     */
    public function testScheduleIsQueued()
    {
        $schedule = factory(Schedule::class)->make();
        $schedule->tasks = collect([factory(Task::class)->make()]);

        $this->repository->shouldReceive('getSchedulesToProcess')->with(Chronos::now()->toAtomString())->once()->andReturn(collect([$schedule]));
        $this->processScheduleService->shouldReceive('handle')->with($schedule)->once()->andReturnNull();

        $display = $this->runCommand($this->command);

        $this->assertNotEmpty($display);
        $this->assertContains(trans('command/messages.schedule.output_line', [
            'schedule' => $schedule->name,
            'hash' => $schedule->hashid,
        ]), $display);
    }

    /**
     * If tasks is an empty collection, don't process it.
     */
    public function testScheduleWithNoTasksIsNotProcessed()
    {
        $schedule = factory(Schedule::class)->make();
        $schedule->tasks = collect([]);

        $this->repository->shouldReceive('getSchedulesToProcess')->with(Chronos::now()->toAtomString())->once()->andReturn(collect([$schedule]));

        $display = $this->runCommand($this->command);

        $this->assertNotEmpty($display);
        $this->assertNotContains(trans('command/messages.schedule.output_line', [
            'schedule' => $schedule->name,
            'hash' => $schedule->hashid,
        ]), $display);
    }

    /**
     * If tasks isn't an instance of a collection, don't process it.
     */
    public function testScheduleWithTasksObjectThatIsNotInstanceOfCollectionIsNotProcessed()
    {
        $schedule = factory(Schedule::class)->make(['tasks' => null]);

        $this->repository->shouldReceive('getSchedulesToProcess')->with(Chronos::now()->toAtomString())->once()->andReturn(collect([$schedule]));

        $display = $this->runCommand($this->command);

        $this->assertNotEmpty($display);
        $this->assertNotContains(trans('command/messages.schedule.output_line', [
            'schedule' => $schedule->name,
            'hash' => $schedule->hashid,
        ]), $display);
    }
}
