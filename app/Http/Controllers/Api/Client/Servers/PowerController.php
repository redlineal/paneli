<?php

namespace Amghost\Http\Controllers\Api\Client\Servers;

use Illuminate\Http\Response;
use Amghost\Models\Server;
use Amghost\Http\Controllers\Api\Client\ClientApiController;
use Amghost\Http\Requests\Api\Client\Servers\SendPowerRequest;
use Amghost\Contracts\Repository\Daemon\PowerRepositoryInterface;

class PowerController extends ClientApiController
{
    /**
     * @var \Amghost\Contracts\Repository\Daemon\PowerRepositoryInterface
     */
    private $repository;

    /**
     * PowerController constructor.
     *
     * @param \Amghost\Contracts\Repository\Daemon\PowerRepositoryInterface $repository
     */
    public function __construct(PowerRepositoryInterface $repository)
    {
        parent::__construct();

        $this->repository = $repository;
    }

    /**
     * Send a power action to a server.
     *
     * @param \Amghost\Http\Requests\Api\Client\Servers\SendPowerRequest $request
     * @return \Illuminate\Http\Response
     *
     * @throws \Amghost\Exceptions\Repository\Daemon\InvalidPowerSignalException
     */
    public function index(SendPowerRequest $request): Response
    {
        $server = $request->getModel(Server::class);
        $token = $request->attributes->get('server_token');

        $this->repository->setServer($server)->setToken($token)->sendSignal($request->input('signal'));

        return $this->returnNoContent();
    }
}
