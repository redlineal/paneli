<?php

namespace Amghost\Repositories\Eloquent;

use Amghost\Models\Task;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Amghost\Contracts\Repository\TaskRepositoryInterface;
use Amghost\Exceptions\Repository\RecordNotFoundException;

class TaskRepository extends EloquentRepository implements TaskRepositoryInterface
{
    /**
     * Return the model backing this repository.
     *
     * @return string
     */
    public function model()
    {
        return Task::class;
    }

    /**
     * Get a task and the server relationship for that task.
     *
     * @param int $id
     * @return \Amghost\Models\Task
     *
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function getTaskForJobProcess(int $id): Task
    {
        try {
            return $this->getBuilder()->with('server.user', 'schedule')->findOrFail($id, $this->getColumns());
        } catch (ModelNotFoundException $exception) {
            throw new RecordNotFoundException;
        }
    }

    /**
     * Returns the next task in a schedule.
     *
     * @param int $schedule
     * @param int $index
     * @return null|\Amghost\Models\Task
     */
    public function getNextTask(int $schedule, int $index)
    {
        return $this->getBuilder()->where('schedule_id', '=', $schedule)
            ->where('sequence_id', '=', $index + 1)
            ->first($this->getColumns());
    }
}
