<?php
/**
 * AMGHOST - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Services\Schedules\Tasks;

use Webmozart\Assert\Assert;
use Amghost\Models\Schedule;
use Amghost\Contracts\Repository\TaskRepositoryInterface;
use Amghost\Exceptions\Service\Schedule\Task\TaskIntervalTooLongException;

class TaskCreationService
{
    const MAX_INTERVAL_TIME_SECONDS = 900;

    /**
     * @var \Amghost\Contracts\Repository\TaskRepositoryInterface
     */
    protected $repository;

    /**
     * TaskCreationService constructor.
     *
     * @param \Amghost\Contracts\Repository\TaskRepositoryInterface $repository
     */
    public function __construct(TaskRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Create a new task that is assigned to a schedule.
     *
     * @param int|\Amghost\Models\Schedule $schedule
     * @param array                            $data
     * @param bool                             $returnModel
     * @return bool|\Amghost\Models\Task
     *
     * @throws \Amghost\Exceptions\Model\DataValidationException
     * @throws \Amghost\Exceptions\Service\Schedule\Task\TaskIntervalTooLongException
     */
    public function handle($schedule, array $data, $returnModel = true)
    {
        Assert::true(($schedule instanceof Schedule || is_digit($schedule)),
            'First argument passed to handle must be numeric or instance of \Amghost\Models\Schedule, received %s.'
        );

        $schedule = ($schedule instanceof Schedule) ? $schedule->id : $schedule;
        $delay = $data['time_interval'] === 'm' ? $data['time_value'] * 60 : $data['time_value'];
        if ($delay > self::MAX_INTERVAL_TIME_SECONDS) {
            throw new TaskIntervalTooLongException(trans('exceptions.tasks.chain_interval_too_long'));
        }

        $repository = ($returnModel) ? $this->repository : $this->repository->withoutFreshModel();
        $task = $repository->create([
            'schedule_id' => $schedule,
            'sequence_id' => $data['sequence_id'],
            'action' => $data['action'],
            'payload' => $data['payload'],
            'time_offset' => $delay,
        ], false);

        return $task;
    }
}
