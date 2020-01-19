<?php

namespace Amghost\Http\Middleware\Server;

use Closure;
use Illuminate\Http\Request;
use Amghost\Contracts\Extensions\HashidsInterface;
use Amghost\Contracts\Repository\ScheduleRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ScheduleBelongsToServer
{
    /**
     * @var \Amghost\Contracts\Extensions\HashidsInterface
     */
    private $hashids;

    /**
     * @var \Amghost\Contracts\Repository\ScheduleRepositoryInterface
     */
    private $repository;

    /**
     * TaskAccess constructor.
     *
     * @param \Amghost\Contracts\Extensions\HashidsInterface            $hashids
     * @param \Amghost\Contracts\Repository\ScheduleRepositoryInterface $repository
     */
    public function __construct(HashidsInterface $hashids, ScheduleRepositoryInterface $repository)
    {
        $this->hashids = $hashids;
        $this->repository = $repository;
    }

    /**
     * Determine if a task is assigned to the active server.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @return mixed
     *
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function handle(Request $request, Closure $next)
    {
        $server = $request->attributes->get('server');

        $scheduleId = $this->hashids->decodeFirst($request->route()->parameter('schedule'), 0);
        $schedule = $this->repository->getScheduleWithTasks($scheduleId);

        if ($schedule->server_id !== $server->id) {
            throw new NotFoundHttpException;
        }

        $request->attributes->set('schedule', $schedule);

        return $next($request);
    }
}
