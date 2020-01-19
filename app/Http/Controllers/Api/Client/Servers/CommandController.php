<?php

namespace Amghost\Http\Controllers\Api\Client\Servers;

use Illuminate\Http\Response;
use Amghost\Models\Server;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use Amghost\Http\Controllers\Api\Client\ClientApiController;
use Amghost\Http\Requests\Api\Client\Servers\SendCommandRequest;
use Amghost\Exceptions\Http\Connection\DaemonConnectionException;
use Amghost\Contracts\Repository\Daemon\CommandRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;

class CommandController extends ClientApiController
{
    /**
     * @var \Amghost\Contracts\Repository\Daemon\CommandRepositoryInterface
     */
    private $repository;

    /**
     * CommandController constructor.
     *
     * @param \Amghost\Contracts\Repository\Daemon\CommandRepositoryInterface $repository
     */
    public function __construct(CommandRepositoryInterface $repository)
    {
        parent::__construct();

        $this->repository = $repository;
    }

    /**
     * Send a command to a running server.
     *
     * @param \Amghost\Http\Requests\Api\Client\Servers\SendCommandRequest $request
     * @return \Illuminate\Http\Response
     *
     * @throws \Amghost\Exceptions\Http\Connection\DaemonConnectionException
     */
    public function index(SendCommandRequest $request): Response
    {
        $server = $request->getModel(Server::class);
        $token = $request->attributes->get('server_token');

        try {
            $this->repository->setServer($server)
                ->setToken($token)
                ->send($request->input('command'));
        } catch (RequestException $exception) {
            if ($exception instanceof ClientException) {
                if ($exception->getResponse() instanceof ResponseInterface && $exception->getResponse()->getStatusCode() === 412) {
                    throw new PreconditionFailedHttpException('Server is not online.');
                }
            }

            throw new DaemonConnectionException($exception);
        }

        return $this->returnNoContent();
    }
}
