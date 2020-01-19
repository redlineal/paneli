<?php

namespace Amghost\Services\Schedules;

use Cron\CronExpression;
use Amghost\Models\Schedule;
use Illuminate\Database\ConnectionInterface;
use Amghost\Contracts\Repository\TaskRepositoryInterface;
use Amghost\Services\Schedules\Tasks\TaskCreationService;
use Amghost\Contracts\Repository\ScheduleRepositoryInterface;

class ScheduleUpdateService
{
    /**
     * @var \Illuminate\Database\ConnectionInterface
     */
    private $connection;

    /**
     * @var \Amghost\Contracts\Repository\ScheduleRepositoryInterface
     */
    private $repository;

    /**
     * @var \Amghost\Services\Schedules\Tasks\TaskCreationService
     */
    private $taskCreationService;

    /**
     * @var \Amghost\Contracts\Repository\TaskRepositoryInterface
     */
    private $taskRepository;

    /**
     * ScheduleUpdateService constructor.
     *
     * @param \Illuminate\Database\ConnectionInterface                      $connection
     * @param \Amghost\Contracts\Repository\ScheduleRepositoryInterface $repository
     * @param \Amghost\Services\Schedules\Tasks\TaskCreationService     $taskCreationService
     * @param \Amghost\Contracts\Repository\TaskRepositoryInterface     $taskRepository
     */
    public function __construct(
        ConnectionInterface $connection,
        ScheduleRepositoryInterface $repository,
        TaskCreationService $taskCreationService,
        TaskRepositoryInterface $taskRepository
    ) {
        $this->connection = $connection;
        $this->repository = $repository;
        $this->taskCreationService = $taskCreationService;
        $this->taskRepository = $taskRepository;
    }

    /**
     * Update an existing schedule by deleting all current tasks and re-inserting the
     * new values.
     *
     * @param \Amghost\Models\Schedule $schedule
     * @param array                        $data
     * @param array                        $tasks
     * @return \Amghost\Models\Schedule
     *
     * @throws \Amghost\Exceptions\Model\DataValidationException
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     * @throws \Amghost\Exceptions\Service\Schedule\Task\TaskIntervalTooLongException
     */
    public function handle(Schedule $schedule, array $data, array $tasks): Schedule
    {
        $data = array_merge($data, [
            'next_run_at' => $this->getCronTimestamp($data),
        ]);

        $this->connection->beginTransaction();

        $schedule = $this->repository->update($schedule->id, $data);
        $this->taskRepository->deleteWhere([['schedule_id', '=', $schedule->id]]);

        foreach ($tasks as $index => $task) {
            $this->taskCreationService->handle($schedule, [
                'time_interval' => array_get($task, 'time_interval'),
                'time_value' => array_get($task, 'time_value'),
                'sequence_id' => $index + 1,
                'action' => array_get($task, 'action'),
                'payload' => array_get($task, 'payload'),
            ], false);
        }

        $this->connection->commit();

        return $schedule;
    }

    /**
     * Return a DateTime object after parsing the cron data provided.
     *
     * @param array $data
     * @return \DateTime
     */
    private function getCronTimestamp(array $data)
    {
        $formattedCron = sprintf('%s %s %s * %s',
            array_get($data, 'cron_minute', '*'),
            array_get($data, 'cron_hour', '*'),
            array_get($data, 'cron_day_of_month', '*'),
            array_get($data, 'cron_day_of_week', '*')
        );

        return CronExpression::factory($formattedCron)->getNextRunDate();
    }
}
