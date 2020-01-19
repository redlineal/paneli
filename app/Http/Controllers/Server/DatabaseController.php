<?php

namespace Amghost\Http\Controllers\Server;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Amghost\Http\Controllers\Controller;
use Amghost\Traits\Controllers\JavascriptInjection;
use Amghost\Services\Databases\DatabasePasswordService;
use Amghost\Services\Databases\DatabaseManagementService;
use Amghost\Services\Databases\DeployServerDatabaseService;
use Amghost\Contracts\Repository\DatabaseRepositoryInterface;
use Amghost\Contracts\Repository\DatabaseHostRepositoryInterface;
use Amghost\Http\Requests\Server\Database\StoreServerDatabaseRequest;
use Amghost\Http\Requests\Server\Database\DeleteServerDatabaseRequest;

class DatabaseController extends Controller
{
    use JavascriptInjection;

    /**
     * @var \Prologue\Alerts\AlertsMessageBag
     */
    private $alert;

    /**
     * @var \Amghost\Services\Databases\DeployServerDatabaseService
     */
    private $deployServerDatabaseService;

    /**
     * @var \Amghost\Contracts\Repository\DatabaseHostRepositoryInterface
     */
    private $databaseHostRepository;

    /**
     * @var \Amghost\Services\Databases\DatabaseManagementService
     */
    private $managementService;

    /**
     * @var \Amghost\Services\Databases\DatabasePasswordService
     */
    private $passwordService;

    /**
     * @var \Amghost\Contracts\Repository\DatabaseRepositoryInterface
     */
    private $repository;

    /**
     * DatabaseController constructor.
     *
     * @param \Prologue\Alerts\AlertsMessageBag                                 $alert
     * @param \Amghost\Services\Databases\DeployServerDatabaseService       $deployServerDatabaseService
     * @param \Amghost\Contracts\Repository\DatabaseHostRepositoryInterface $databaseHostRepository
     * @param \Amghost\Services\Databases\DatabaseManagementService         $managementService
     * @param \Amghost\Services\Databases\DatabasePasswordService           $passwordService
     * @param \Amghost\Contracts\Repository\DatabaseRepositoryInterface     $repository
     */
    public function __construct(
        AlertsMessageBag $alert,
        DeployServerDatabaseService $deployServerDatabaseService,
        DatabaseHostRepositoryInterface $databaseHostRepository,
        DatabaseManagementService $managementService,
        DatabasePasswordService $passwordService,
        DatabaseRepositoryInterface $repository
    ) {
        $this->alert = $alert;
        $this->databaseHostRepository = $databaseHostRepository;
        $this->deployServerDatabaseService = $deployServerDatabaseService;
        $this->managementService = $managementService;
        $this->passwordService = $passwordService;
        $this->repository = $repository;
    }

    /**
     * Render the database listing for a server.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Request $request): View
    {
        $server = $request->attributes->get('server');
        $this->authorize('view-databases', $server);
        $this->setRequest($request)->injectJavascript();

        $canCreateDatabase = config('amghost.client_features.databases.enabled');
        $allowRandom = config('amghost.client_features.databases.allow_random');

        if ($this->databaseHostRepository->findCountWhere([['node_id', '=', $server->node_id]]) === 0) {
            if ($canCreateDatabase && ! $allowRandom) {
                $canCreateDatabase = false;
            }
        }

        $databases = $this->repository->getDatabasesForServer($server->id);

        return view('server.databases.index', [
            'allowCreation' => $canCreateDatabase,
            'overLimit' => ! is_null($server->database_limit) && count($databases) >= $server->database_limit,
            'databases' => $databases,
        ]);
    }

    /**
     * Handle a request from a user to create a new database for the server.
     *
     * @param \Amghost\Http\Requests\Server\Database\StoreServerDatabaseRequest $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Exception
     * @throws \Amghost\Exceptions\Service\Database\DatabaseClientFeatureNotEnabledException
     */
    public function store(StoreServerDatabaseRequest $request): RedirectResponse
    {
        $this->deployServerDatabaseService->handle($request->getServer(), $request->validated());

        $this->alert->success('Successfully created a new database.')->flash();

        return redirect()->route('server.databases.index', $request->getServer()->uuidShort);
    }

    /**
     * Handle a request to update the password for a specific database.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(Request $request): JsonResponse
    {
        $this->authorize('reset-db-password', $request->attributes->get('server'));

        $password = $this->passwordService->handle($request->attributes->get('database'));

        return response()->json(['password' => $password]);
    }

    /**
     * Delete a database for this server from the SQL server and Panel database.
     *
     * @param \Amghost\Http\Requests\Server\Database\DeleteServerDatabaseRequest $request
     * @return \Illuminate\Http\Response
     *
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function delete(DeleteServerDatabaseRequest $request): Response
    {
        $this->managementService->delete($request->attributes->get('database')->id);

        return response('', Response::HTTP_NO_CONTENT);
    }
}
