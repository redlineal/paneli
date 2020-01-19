<?php
/**
 * Amghost - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Http\Controllers\Admin;

use Amghost\Models\Location;
use Prologue\Alerts\AlertsMessageBag;
use Amghost\Exceptions\DisplayException;
use Amghost\Http\Controllers\Controller;
use Amghost\Http\Requests\Admin\LocationFormRequest;
use Amghost\Services\Locations\LocationUpdateService;
use Amghost\Services\Locations\LocationCreationService;
use Amghost\Services\Locations\LocationDeletionService;
use Amghost\Contracts\Repository\LocationRepositoryInterface;

class LocationController extends Controller
{
    /**
     * @var \Prologue\Alerts\AlertsMessageBag
     */
    protected $alert;

    /**
     * @var \Amghost\Services\Locations\LocationCreationService
     */
    protected $creationService;

    /**
     * @var \Amghost\Services\Locations\LocationDeletionService
     */
    protected $deletionService;

    /**
     * @var \Amghost\Contracts\Repository\LocationRepositoryInterface
     */
    protected $repository;

    /**
     * @var \Amghost\Services\Locations\LocationUpdateService
     */
    protected $updateService;

    /**
     * LocationController constructor.
     *
     * @param \Prologue\Alerts\AlertsMessageBag                             $alert
     * @param \Amghost\Services\Locations\LocationCreationService       $creationService
     * @param \Amghost\Services\Locations\LocationDeletionService       $deletionService
     * @param \Amghost\Contracts\Repository\LocationRepositoryInterface $repository
     * @param \Amghost\Services\Locations\LocationUpdateService         $updateService
     */
    public function __construct(
        AlertsMessageBag $alert,
        LocationCreationService $creationService,
        LocationDeletionService $deletionService,
        LocationRepositoryInterface $repository,
        LocationUpdateService $updateService
    ) {
        $this->alert = $alert;
        $this->creationService = $creationService;
        $this->deletionService = $deletionService;
        $this->repository = $repository;
        $this->updateService = $updateService;
    }

    /**
     * Return the location overview page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('admin.locations.index', [
            'locations' => $this->repository->getAllWithDetails(),
        ]);
    }

    /**
     * Return the location view page.
     *
     * @param int $id
     * @return \Illuminate\View\View
     *
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function view($id)
    {
        return view('admin.locations.view', [
            'location' => $this->repository->getWithNodes($id),
        ]);
    }

    /**
     * Handle request to create new location.
     *
     * @param \Amghost\Http\Requests\Admin\LocationFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Throwable
     */
    public function create(LocationFormRequest $request)
    {
        $location = $this->creationService->handle($request->normalize());
        $this->alert->success('Location was created successfully.')->flash();

        return redirect()->route('admin.locations.view', $location->id);
    }

    /**
     * Handle request to update or delete location.
     *
     * @param \Amghost\Http\Requests\Admin\LocationFormRequest $request
     * @param \Amghost\Models\Location                         $location
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Throwable
     */
    public function update(LocationFormRequest $request, Location $location)
    {
        if ($request->input('action') === 'delete') {
            return $this->delete($location);
        }

        $this->updateService->handle($location->id, $request->normalize());
        $this->alert->success('Location was updated successfully.')->flash();

        return redirect()->route('admin.locations.view', $location->id);
    }

    /**
     * Delete a location from the system.
     *
     * @param \Amghost\Models\Location $location
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Exception
     * @throws \Amghost\Exceptions\DisplayException
     */
    public function delete(Location $location)
    {
        try {
            $this->deletionService->handle($location->id);

            return redirect()->route('admin.locations');
        } catch (DisplayException $ex) {
            $this->alert->danger($ex->getMessage())->flash();
        }

        return redirect()->route('admin.locations.view', $location->id);
    }
}
