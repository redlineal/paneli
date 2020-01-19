<?php
/**
 * Amghost - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Http\Controllers\Admin\Nests;

use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Amghost\Http\Controllers\Controller;
use Amghost\Services\Nests\NestUpdateService;
use Amghost\Services\Nests\NestCreationService;
use Amghost\Services\Nests\NestDeletionService;
use Amghost\Contracts\Repository\NestRepositoryInterface;
use Amghost\Http\Requests\Admin\Nest\StoreNestFormRequest;

class NestController extends Controller
{
    /**
     * @var \Prologue\Alerts\AlertsMessageBag
     */
    protected $alert;

    /**
     * @var \Amghost\Services\Nests\NestCreationService
     */
    protected $nestCreationService;

    /**
     * @var \Amghost\Services\Nests\NestDeletionService
     */
    protected $nestDeletionService;

    /**
     * @var \Amghost\Contracts\Repository\NestRepositoryInterface
     */
    protected $repository;

    /**
     * @var \Amghost\Services\Nests\NestUpdateService
     */
    protected $nestUpdateService;

    /**
     * NestController constructor.
     *
     * @param \Prologue\Alerts\AlertsMessageBag                         $alert
     * @param \Amghost\Services\Nests\NestCreationService           $nestCreationService
     * @param \Amghost\Services\Nests\NestDeletionService           $nestDeletionService
     * @param \Amghost\Contracts\Repository\NestRepositoryInterface $repository
     * @param \Amghost\Services\Nests\NestUpdateService             $nestUpdateService
     */
    public function __construct(
        AlertsMessageBag $alert,
        NestCreationService $nestCreationService,
        NestDeletionService $nestDeletionService,
        NestRepositoryInterface $repository,
        NestUpdateService $nestUpdateService
    ) {
        $this->alert = $alert;
        $this->nestDeletionService = $nestDeletionService;
        $this->nestCreationService = $nestCreationService;
        $this->nestUpdateService = $nestUpdateService;
        $this->repository = $repository;
    }

    /**
     * Render nest listing page.
     *
     * @return \Illuminate\View\View
     *
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function index(): View
    {
        return view('admin.nests.index', [
            'nests' => $this->repository->getWithCounts(),
        ]);
    }

    /**
     * Render nest creation page.
     *
     * @return \Illuminate\View\View
     */
    public function create(): View
    {
        return view('admin.nests.new');
    }

    /**
     * Handle the storage of a new nest.
     *
     * @param \Amghost\Http\Requests\Admin\Nest\StoreNestFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Amghost\Exceptions\Model\DataValidationException
     */
    public function store(StoreNestFormRequest $request): RedirectResponse
    {
        $nest = $this->nestCreationService->handle($request->normalize());
        $this->alert->success(trans('admin/nests.notices.created', ['name' => $nest->name]))->flash();

        return redirect()->route('admin.nests.view', $nest->id);
    }

    /**
     * Return details about a nest including all of the eggs and servers per egg.
     *
     * @param int $nest
     * @return \Illuminate\View\View
     *
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function view(int $nest): View
    {
        return view('admin.nests.view', [
            'nest' => $this->repository->getWithEggServers($nest),
        ]);
    }

    /**
     * Handle request to update a nest.
     *
     * @param \Amghost\Http\Requests\Admin\Nest\StoreNestFormRequest $request
     * @param int                                                        $nest
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Amghost\Exceptions\Model\DataValidationException
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function update(StoreNestFormRequest $request, int $nest): RedirectResponse
    {
        $this->nestUpdateService->handle($nest, $request->normalize());
        $this->alert->success(trans('admin/nests.notices.updated'))->flash();

        return redirect()->route('admin.nests.view', $nest);
    }

    /**
     * Handle request to delete a nest.
     *
     * @param int $nest
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Amghost\Exceptions\Service\HasActiveServersException
     */
    public function destroy(int $nest): RedirectResponse
    {
        $this->nestDeletionService->handle($nest);
        $this->alert->success(trans('admin/nests.notices.deleted'))->flash();

        return redirect()->route('admin.nests');
    }
}
