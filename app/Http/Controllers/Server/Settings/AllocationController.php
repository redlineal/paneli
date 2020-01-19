<?php

namespace Amghost\Http\Controllers\Server\Settings;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Amghost\Http\Controllers\Controller;
use Amghost\Contracts\Extensions\HashidsInterface;
use Amghost\Traits\Controllers\JavascriptInjection;
use Amghost\Services\Allocations\SetDefaultAllocationService;
use Amghost\Contracts\Repository\AllocationRepositoryInterface;
use Amghost\Exceptions\Service\Allocation\AllocationDoesNotBelongToServerException;

class AllocationController extends Controller
{
    use JavascriptInjection;

    /**
     * @var \Amghost\Services\Allocations\SetDefaultAllocationService
     */
    private $defaultAllocationService;

    /**
     * @var \Amghost\Contracts\Extensions\HashidsInterface
     */
    private $hashids;

    /**
     * @var \Amghost\Contracts\Repository\AllocationRepositoryInterface
     */
    private $repository;

    /**
     * AllocationController constructor.
     *
     * @param \Amghost\Contracts\Repository\AllocationRepositoryInterface $repository
     * @param \Amghost\Contracts\Extensions\HashidsInterface              $hashids
     * @param \Amghost\Services\Allocations\SetDefaultAllocationService   $defaultAllocationService
     */
    public function __construct(
        AllocationRepositoryInterface $repository,
        HashidsInterface $hashids,
        SetDefaultAllocationService $defaultAllocationService
    ) {
        $this->defaultAllocationService = $defaultAllocationService;
        $this->hashids = $hashids;
        $this->repository = $repository;
    }

    /**
     * Render the allocation management overview page for a server.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Request $request): View
    {
        $server = $request->attributes->get('server');
        $this->authorize('view-allocations', $server);
        $this->setRequest($request)->injectJavascript();

        return view('server.settings.allocation', [
            'allocations' => $this->repository->findWhere([['server_id', '=', $server->id]]),
        ]);
    }

    /**
     * Update the default allocation for a server.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Amghost\Exceptions\Http\Connection\DaemonConnectionException
     * @throws \Amghost\Exceptions\Model\DataValidationException
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function update(Request $request): JsonResponse
    {
        $server = $request->attributes->get('server');
        $this->authorize('edit-allocation', $server);

        $allocation = $this->hashids->decodeFirst($request->input('allocation'), 0);

        try {
            $this->defaultAllocationService->handle($server->id, $allocation);
        } catch (AllocationDoesNotBelongToServerException $exception) {
            return response()->json(['error' => 'No matching allocation was located for this server.'], 404);
        }

        return response()->json();
    }
}
