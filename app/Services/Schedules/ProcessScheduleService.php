<?php

namespace Amghost\Services\Schedules;

use Cron\CronExpression;
use Amghost\Models\Schedule;
use Illuminate\Contracts\Bus\Dispatcher;
use Amghost\Jobs\Schedule\RunTaskJob;
use Amghost\Contracts\Repository\TaskRepositoryInterface;
use Amghost\Contracts\Repository\ScheduleRepositoryInterface;

class ProcessScheduleService
{
    /**
     * @var \Illuminate\Contracts\Bus\Dispatcher
     */
    private $dispatcher;

    /**
     * @var \Amghost\Contracts\Repository\ScheduleRepositoryInterface
     */
    private $scheduleRepository;

    /**
     * @var \Amghost\Contracts\Repository\TaskRepositoryInterface
     */
    private $taskRepository;

    /**
     * ProcessScheduleService constructor.
     *
     * @param \Illuminate\Contracts\Bus\Dispatcher                          $dispatcher
     * @param \Amghost\Contracts\Repository\ScheduleRepositoryInterface $scheduleRepository
     * @param \Amghost\Contracts\Repository\TaskRepositoryInterface     $taskRepository
     */
    public function __construct(
        Dispatcher $dispatcher,
        ScheduleRepositoryInterface $scheduleRepository,
        TaskRepositoryInterface $taskRepository
    ) {
        $this->dispatcher = $dispatcher;
        $this->scheduleRepository = $scheduleRepository;
        $this->taskRepository = $taskRepository;
    }

    /**
     * Process a schedule and push the first task onto the queue worker.
     *
     * @param \Amghost\Models\Schedule $schedule
     *
     * @throws \Amghost\Exceptions\Model\DataValidationException
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function handle(Schedule $schedule)
    {
        $this->scheduleRepository->loadTasks($schedule);

        /** @var \Amghost\Models\Task $task */
        $task = $schedule->getRelation('tasks')->where('sequence_id', 1)->first();

        $formattedCron = sprintf('%s %s %s * %s',
            $schedule->cron_minute,
            $schedule->cron_hour,
            $schedule->cron_day_of_month,
            $schedule->cron_day_of_week
        );

        $this->scheduleRepository->update($schedule->id, [
            'is_processing' => true,
            'next_run_at' => CronExpression::factory($formattedCron)->getNextRunDate(),
        ]);

        $this->taskRepository->update($task->id, ['is_queued' => true]);

        $this->dispatcher->dispatch(
            (new RunTaskJob($task->id, $schedule->id))->delay($task->time_offset)
        );
    }
}
