<?php

namespace Amghost\Contracts\Repository;

use Amghost\Models\Schedule;
use Illuminate\Support\Collection;

interface ScheduleRepositoryInterface extends RepositoryInterface
{
    /**
     * Return all of the schedules for a given server.
     *
     * @param int $server
     * @return \Illuminate\Support\Collection
     */
    public function findServerSchedules(int $server): Collection;

    /**
     * Load the tasks relationship onto the Schedule module if they are not
     * already present.
     *
     * @param \Amghost\Models\Schedule $schedule
     * @param bool                         $refresh
     * @return \Amghost\Models\Schedule
     */
    public function loadTasks(Schedule $schedule, bool $refresh = false): Schedule;

    /**
     * Return a schedule model with all of the associated tasks as a relationship.
     *
     * @param int $schedule
     * @return \Amghost\Models\Schedule
     *
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function getScheduleWithTasks(int $schedule): Schedule;

    /**
     * Return all of the schedules that should be processed.
     *
     * @param string $timestamp
     * @return \Illuminate\Support\Collection
     */
    public function getSchedulesToProcess(string $timestamp): Collection;
}
