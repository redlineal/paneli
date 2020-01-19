<?php

namespace Amghost\Http\Controllers\Api\Application\Servers;

use Illuminate\Http\Response;
use Amghost\Models\Server;
use Amghost\Services\Servers\SuspensionService;
use Amghost\Services\Servers\ReinstallServerService;
use Amghost\Services\Servers\ContainerRebuildService;
use Amghost\Http\Requests\Api\Application\Servers\ServerWriteRequest;
use Amghost\Http\Controllers\Api\Application\ApplicationApiController;

class ServerManagementController extends ApplicationApiController
{
    /**
     * @var \Amghost\Services\Servers\ContainerRebuildService
     */
    private $rebuildService;

    /**
     * @var \Amghost\Services\Servers\ReinstallServerService
     */
    private $reinstallServerService;

    /**
     * @var \Amghost\Services\Servers\SuspensionService
     */
    private $suspensionService;

    /**
     * SuspensionController constructor.
     *
     * @param \Amghost\Services\Servers\ContainerRebuildService $rebuildService
     * @param \Amghost\Services\Servers\ReinstallServerService  $reinstallServerService
     * @param \Amghost\Services\Servers\SuspensionService       $suspensionService
     */
    public function __construct(
        ContainerRebuildService $rebuildService,
        ReinstallServerService $reinstallServerService,
        SuspensionService $suspensionService
    ) {
        parent::__construct();

        $this->rebuildService = $rebuildService;
        $this->reinstallServerService = $reinstallServerService;
        $this->suspensionService = $suspensionService;
    }

    /**
     * Suspend a server on the Panel.
     *
     * @param \Amghost\Http\Requests\Api\Application\Servers\ServerWriteRequest $request
     * @return \Illuminate\Http\Response
     *
     * @throws \Amghost\Exceptions\DisplayException
     * @throws \Amghost\Exceptions\Model\DataValidationException
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function suspend(ServerWriteRequest $request): Response
    {
        $this->suspensionService->toggle($request->getModel(Server::class), SuspensionService::ACTION_SUSPEND);

        return $this->returnNoContent();
    }

    /**
     * Unsuspend a server on the Panel.
     *
     * @param \Amghost\Http\Requests\Api\Application\Servers\ServerWriteRequest $request
     * @return \Illuminate\Http\Response
     *
     * @throws \Amghost\Exceptions\DisplayException
     * @throws \Amghost\Exceptions\Model\DataValidationException
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function unsuspend(ServerWriteRequest $request): Response
    {
        $this->suspensionService->toggle($request->getModel(Server::class), SuspensionService::ACTION_UNSUSPEND);

        return $this->returnNoContent();
    }

    /**
     * Mark a server as needing to be reinstalled.
     *
     * @param \Amghost\Http\Requests\Api\Application\Servers\ServerWriteRequest $request
     * @return \Illuminate\Http\Response
     *
     * @throws \Amghost\Exceptions\DisplayException
     * @throws \Amghost\Exceptions\Model\DataValidationException
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function reinstall(ServerWriteRequest $request): Response
    {
        $this->reinstallServerService->reinstall($request->getModel(Server::class));

        return $this->returnNoContent();
    }

    /**
     * Mark a server as needing its container rebuilt the next time it is started.
     *
     * @param \Amghost\Http\Requests\Api\Application\Servers\ServerWriteRequest $request
     * @return \Illuminate\Http\Response
     *
     * @throws \Amghost\Exceptions\Http\Connection\DaemonConnectionException
     */
    public function rebuild(ServerWriteRequest $request): Response
    {
        $this->rebuildService->handle($request->getModel(Server::class));

        return $this->returnNoContent();
    }
}
