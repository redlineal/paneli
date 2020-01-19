<?php

namespace Tests\Unit\Jobs\Schedule;

use Mockery as m;
use Tests\TestCase;
use Cake\Chronos\Chronos;
use Amghost\Models\Task;
use Amghost\Models\User;
use GuzzleHttp\Psr7\Response;
use Amghost\Models\Server;
use Amghost\Models\Schedule;
use Illuminate\Support\Facades\Bus;
use Amghost\Jobs\Schedule\RunTaskJob;
use Illuminate\Contracts\Config\Repository;
use Amghost\Contracts\Repository\TaskRepositoryInterface;
use Amghost\Services\DaemonKeys\DaemonKeyProviderService;
use Amghost\Contracts\Repository\ScheduleRepositoryInterface;
use Amghost\Contracts\Repository\Daemon\PowerRepositoryInterface;
use Amghost\Contracts\Repository\Daemon\CommandRepositoryInterface;

class RunTaskJobTest extends TestCase
{
    /**
     * @var \Amghost\Contracts\Repository\Daemon\CommandRepositoryInterface|\Mockery\Mock
     */
    protected $commandRepository;

    /**
     * @var \Illuminate\Contracts\Config\Repository|\Mockery\Mock
     */
    protected $config;

    /**
     * @var \Amghost\Services\DaemonKeys\DaemonKeyProviderService|\Mockery\Mock
     */
    protected $keyProviderService;

    /**
     * @var \Amghost\Contracts\Repository\Daemon\PowerRepositoryInterface|\Mockery\Mock
     */
    protected $powerRepository;

    /**
     * @var \Amghost\Contracts\Repository\ScheduleRepositoryInterface|\Mockery\Mock
     */
    protected $scheduleRepository;

    /**
     * @var \Amghost\Contracts\Repository\TaskRepositoryInterface|\Mockery\Mock
     */
    protected $taskRepository;

    /**
     * Setup tests.
     */
    public function setUp()
    {
        parent::setUp();
        Bus::fake();
        Chronos::setTestNow(Chronos::now());

        $this->commandRepository = m::mock(CommandRepositoryInterface::class);
        $this->config = m::mock(Repository::class);
        $this->keyProviderService = m::mock(DaemonKeyProviderService::class);
        $this->powerRepository = m::mock(PowerRepositoryInterface::class);
        $this->scheduleRepository = m::mock(ScheduleRepositoryInterface::class);
        $this->taskRepository = m::mock(TaskRepositoryInterface::class);

        $this->app->instance(Repository::class, $this->config);
        $this->app->instance(TaskRepositoryInterface::class, $this->taskRepository);
        $this->app->instance(ScheduleRepositoryInterface::class, $this->scheduleRepository);
    }

    /**
     * Test power option passed to job.
     */
    public function testPowerAction()
    {
        $schedule = factory(Schedule::class)->make();
        $task = factory(Task::class)->make(['action' => 'power', 'sequence_id' => 1]);
        $task->setRelation('server', $server = factory(Server::class)->make());
        $task->setRelation('schedule', $schedule);
        $server->setRelation('user', factory(User::class)->make());

        $this->taskRepository->shouldReceive('getTaskForJobProcess')->with($task->id)->once()->andReturn($task);
        $this->keyProviderService->shouldReceive('handle')->with($server, $server->user)->once()->andReturn('123456');
        $this->powerRepository->shouldReceive('setServer')->with($task->server)->once()->andReturnSelf()
            ->shouldReceive('setToken')->with('123456')->once()->andReturnSelf()
            ->shouldReceive('sendSignal')->with($task->payload)->once()->andReturn(new Response);

        $this->taskRepository->shouldReceive('update')->with($task->id, ['is_queued' => false])->once()->andReturnNull();
        $this->taskRepository->shouldReceive('getNextTask')->with($schedule->id, $task->sequence_id)->once()->andReturnNull();

        $this->scheduleRepository->shouldReceive('withoutFreshModel->update')->with($schedule->id, [
            'is_processing' => false,
            'last_run_at' => Chronos::now()->toDateTimeString(),
        ])->once()->andReturnNull();

        $this->getJobInstance($task->id, $schedule->id);

        Bus::assertNotDispatched(RunTaskJob::class);
    }

    /**
     * Test command action passed to job.
     */
    public function testCommandAction()
    {
        $schedule = factory(Schedule::class)->make();
        $task = factory(Task::class)->make(['action' => 'command', 'sequence_id' => 1]);
        $task->setRelation('server', $server = factory(Server::class)->make());
        $task->setRelation('schedule', $schedule);
        $server->setRelation('user', factory(User::class)->make());

        $this->taskRepository->shouldReceive('getTaskForJobProcess')->with($task->id)->once()->andReturn($task);
        $this->keyProviderService->shouldReceive('handle')->with($server, $server->user)->once()->andReturn('123456');
        $this->commandRepository->shouldReceive('setServer')->with($task->server)->once()->andReturnSelf()
            ->shouldReceive('setToken')->with('123456')->once()->andReturnSelf()
            ->shouldReceive('send')->with($task->payload)->once()->andReturn(new Response);

        $this->taskRepository->shouldReceive('update')->with($task->id, ['is_queued' => false])->once()->andReturnNull();
        $this->taskRepository->shouldReceive('getNextTask')->with($schedule->id, $task->sequence_id)->once()->andReturnNull();

        $this->scheduleRepository->shouldReceive('withoutFreshModel->update')->with($schedule->id, [
            'is_processing' => false,
            'last_run_at' => Chronos::now()->toDateTimeString(),
        ])->once()->andReturnNull();

        $this->getJobInstance($task->id, $schedule->id);

        Bus::assertNotDispatched(RunTaskJob::class);
    }

    /**
     * Test that the next task in the list is queued if the current one is not the last.
     */
    public function testNextTaskQueuedIfExists()
    {
        $schedule = factory(Schedule::class)->make();
        $task = factory(Task::class)->make(['action' => 'command', 'sequence_id' => 1]);
        $task->setRelation('server', $server = factory(Server::class)->make());
        $task->setRelation('schedule', $schedule);
        $server->setRelation('user', factory(User::class)->make());

        $this->taskRepository->shouldReceive('getTaskForJobProcess')->with($task->id)->once()->andReturn($task);
        $this->keyProviderService->shouldReceive('handle')->with($server, $server->user)->once()->andReturn('123456');
        $this->commandRepository->shouldReceive('setServer')->with($task->server)->once()->andReturnSelf()
            ->shouldReceive('setToken')->with('123456')->once()->andReturnSelf()
            ->shouldReceive('send')->with($task->payload)->once()->andReturn(new Response);

        $this->taskRepository->shouldReceive('update')->with($task->id, ['is_queued' => false])->once()->andReturnNull();

        $nextTask = factory(Task::class)->make();
        $this->taskRepository->shouldReceive('getNextTask')->with($schedule->id, $task->sequence_id)->once()->andReturn($nextTask);
        $this->taskRepository->shouldReceive('update')->with($nextTask->id, [
            'is_queued' => true,
        ])->once()->andReturnNull();

        $this->getJobInstance($task->id, $schedule->id);

        Bus::assertDispatched(RunTaskJob::class, function ($job) use ($nextTask, $schedule) {
            $this->assertEquals($nextTask->id, $job->task, 'Assert correct task ID is passed to job.');
            $this->assertEquals($schedule->id, $job->schedule, 'Assert correct schedule ID is passed to job.');
            $this->assertEquals($nextTask->time_offset, $job->delay, 'Assert correct job delay time is set.');

            return true;
        });
    }

    /**
     * Test that an exception is thrown if an invalid task action is supplied.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Cannot run a task that points to a non-existent action.
     */
    public function testInvalidActionPassedToJob()
    {
        $schedule = factory(Schedule::class)->make();
        $task = factory(Task::class)->make(['action' => 'invalid', 'sequence_id' => 1]);
        $task->setRelation('server', $server = factory(Server::class)->make());
        $task->setRelation('schedule', $schedule);
        $server->setRelation('user', factory(User::class)->make());

        $this->taskRepository->shouldReceive('getTaskForJobProcess')->with($task->id)->once()->andReturn($task);

        $this->getJobInstance($task->id, 1234);
    }

    /**
     * Test that a schedule marked as disabled does not get processed.
     */
    public function testScheduleMarkedAsDisabledDoesNotProcess()
    {
        $schedule = factory(Schedule::class)->make(['is_active' => false]);
        $task = factory(Task::class)->make(['action' => 'invalid', 'sequence_id' => 1]);
        $task->setRelation('server', $server = factory(Server::class)->make());
        $task->setRelation('schedule', $schedule);
        $server->setRelation('user', factory(User::class)->make());

        $this->taskRepository->shouldReceive('getTaskForJobProcess')->with($task->id)->once()->andReturn($task);

        $this->scheduleRepository->shouldReceive('withoutFreshModel->update')->with($schedule->id, [
            'is_processing' => false,
            'last_run_at' => Chronos::now()->toDateTimeString(),
        ])->once()->andReturn(1);

        $this->taskRepository->shouldReceive('update')->with($task->id, ['is_queued' => false])->once()->andReturn(1);

        $this->getJobInstance($task->id, $schedule->id);
        $this->assertTrue(true);
    }

    /**
     * Run the job using the mocks provided.
     *
     * @param int $task
     * @param int $schedule
     *
     * @throws \Amghost\Exceptions\Model\DataValidationException
     * @throws \Amghost\Exceptions\Repository\Daemon\InvalidPowerSignalException
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    private function getJobInstance($task, $schedule)
    {
        return (new RunTaskJob($task, $schedule))->handle(
            $this->commandRepository,
            $this->keyProviderService,
            $this->powerRepository,
            $this->taskRepository
        );
    }
}
