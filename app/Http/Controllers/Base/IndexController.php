<?php

namespace Amghost\Http\Controllers\Base;

use Illuminate\Http\Request;
use Amghost\Models\User;
use Illuminate\Http\Response;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Amghost\Http\Controllers\Controller;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Amghost\Services\DaemonKeys\DaemonKeyProviderService;
use Amghost\Contracts\Repository\ServerRepositoryInterface;
use Amghost\Contracts\Repository\Daemon\ServerRepositoryInterface as DaemonServerRepositoryInterface;

class IndexController extends Controller
{
    /**
     * @var \Amghost\Contracts\Repository\Daemon\ServerRepositoryInterface
     */
    protected $daemonRepository;

    /**
     * @var \Amghost\Services\DaemonKeys\DaemonKeyProviderService
     */
    protected $keyProviderService;

    /**
     * @var \Amghost\Contracts\Repository\ServerRepositoryInterface
     */
    protected $repository;

    /**
     * IndexController constructor.
     *
     * @param \Amghost\Services\DaemonKeys\DaemonKeyProviderService          $keyProviderService
     * @param \Amghost\Contracts\Repository\Daemon\ServerRepositoryInterface $daemonRepository
     * @param \Amghost\Contracts\Repository\ServerRepositoryInterface        $repository
     */
    public function __construct(
        DaemonKeyProviderService $keyProviderService,
        DaemonServerRepositoryInterface $daemonRepository,
        ServerRepositoryInterface $repository
    ) {
        $this->daemonRepository = $daemonRepository;
        $this->keyProviderService = $keyProviderService;
        $this->repository = $repository;
    }

    /**
     * Returns listing of user's servers.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function getIndex(Request $request)
    {
        $servers = $this->repository->setSearchTerm($request->input('query'))->filterUserAccessServers(
            $request->user(), User::FILTER_LEVEL_ALL, config('pterodactyl.paginate.frontend.servers')
        );

        return view('base.index', ['servers' => $servers]);
    }

    /**
     * Returns status of the server in a JSON response used for populating active status list.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $uuid
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function status(Request $request, $uuid)
    {
        $server = $this->repository->findFirstWhere([['uuidShort', '=', $uuid]]);
        $token = $this->keyProviderService->handle($server, $request->user());

        if (! $server->installed) {
            return response()->json(['status' => 20]);
        } elseif ($server->suspended) {
            return response()->json(['status' => 30]);
        }

        try {
            $response = $this->daemonRepository->setServer($server)->setToken($token)->details();
        } catch (ConnectException $exception) {
            throw new HttpException(Response::HTTP_GATEWAY_TIMEOUT, $exception->getMessage());
        } catch (RequestException $exception) {
            throw new HttpException(500, $exception->getMessage());
        }

        return response()->json(json_decode($response->getBody()));
    }
}
