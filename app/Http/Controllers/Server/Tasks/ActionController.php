<?php

namespace Amghost\Http\Controllers\Server\Tasks;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Amghost\Http\Controllers\Controller;
use Amghost\Services\Schedules\ProcessScheduleService;
use Amghost\Contracts\Repository\ScheduleRepositoryInterface;

class ActionController extends Controller
{
    /**
     * @var \Amghost\Services\Schedules\ProcessScheduleService
     */
    private $processScheduleService;

    /**
     * @var \Amghost\Contracts\Repository\ScheduleRepositoryInterface
     */
    private $repository;

    /**
     * ActionController constructor.
     *
     * @param \Amghost\Services\Schedules\ProcessScheduleService        $processScheduleService
     * @param \Amghost\Contracts\Repository\ScheduleRepositoryInterface $repository
     */
    public function __construct(ProcessScheduleService $processScheduleService, ScheduleRepositoryInterface $repository)
    {
        $this->processScheduleService = $processScheduleService;
        $this->repository = $repository;
    }

    /**
     * Toggle a task to be active or inactive for a given server.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Amghost\Exceptions\Model\DataValidationException
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function toggle(Request $request): Response
    {
        $server = $request->attributes->get('server');
        $schedule = $request->attributes->get('schedule');
        $this->authorize('toggle-schedule', $server);

        $this->repository->update($schedule->id, [
            'is_active' => ! $schedule->is_active,
        ]);

        return response('', 204);
    }

    /**
     * Trigger a schedule to run now.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Amghost\Exceptions\Model\DataValidationException
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function trigger(Request $request): Response
    {
        $server = $request->attributes->get('server');
        $this->authorize('toggle-schedule', $server);

        $this->processScheduleService->handle(
            $request->attributes->get('schedule')
        );

        return response('', 204);
    }
}
