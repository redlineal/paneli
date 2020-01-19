<?php

namespace Amghost\Http\Controllers\Api\Application\Servers;

use Amghost\Models\User;
use Amghost\Models\Server;
use Amghost\Services\Servers\StartupModificationService;
use Amghost\Transformers\Api\Application\ServerTransformer;
use Amghost\Http\Controllers\Api\Application\ApplicationApiController;
use Amghost\Http\Requests\Api\Application\Servers\UpdateServerStartupRequest;

class StartupController extends ApplicationApiController
{
    /**
     * @var \Amghost\Services\Servers\StartupModificationService
     */
    private $modificationService;

    /**
     * StartupController constructor.
     *
     * @param \Amghost\Services\Servers\StartupModificationService $modificationService
     */
    public function __construct(StartupModificationService $modificationService)
    {
        parent::__construct();

        $this->modificationService = $modificationService;
    }

    /**
     * Update the startup and environment settings for a specific server.
     *
     * @param \Amghost\Http\Requests\Api\Application\Servers\UpdateServerStartupRequest $request
     * @return array
     *
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Amghost\Exceptions\Http\Connection\DaemonConnectionException
     * @throws \Amghost\Exceptions\Model\DataValidationException
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function index(UpdateServerStartupRequest $request): array
    {
        $server = $this->modificationService
            ->setUserLevel(User::USER_LEVEL_ADMIN)
            ->handle($request->getModel(Server::class), $request->validated());

        return $this->fractal->item($server)
            ->transformWith($this->getTransformer(ServerTransformer::class))
            ->toArray();
    }
}
