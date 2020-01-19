<?php

namespace Amghost\Contracts\Repository;

use Amghost\Models\Task;

interface TaskRepositoryInterface extends RepositoryInterface
{
    /**
     * Get a task and the server relationship for that task.
     *
     * @param int $id
     * @return \Amghost\Models\Task
     *
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function getTaskForJobProcess(int $id): Task;

    /**
     * Returns the next task in a schedule.
     *
     * @param int $schedule
     * @param int $index
     * @return null|\Amghost\Models\Task
     */
    public function getNextTask(int $schedule, int $index);
}
