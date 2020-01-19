<?php

namespace Amghost\Http\Controllers\Api\Application\Locations;

use Illuminate\Http\Response;
use Amghost\Models\Location;
use Illuminate\Http\JsonResponse;
use Amghost\Services\Locations\LocationUpdateService;
use Amghost\Services\Locations\LocationCreationService;
use Amghost\Services\Locations\LocationDeletionService;
use Amghost\Contracts\Repository\LocationRepositoryInterface;
use Amghost\Transformers\Api\Application\LocationTransformer;
use Amghost\Http\Controllers\Api\Application\ApplicationApiController;
use Amghost\Http\Requests\Api\Application\Locations\GetLocationRequest;
use Amghost\Http\Requests\Api\Application\Locations\GetLocationsRequest;
use Amghost\Http\Requests\Api\Application\Locations\StoreLocationRequest;
use Amghost\Http\Requests\Api\Application\Locations\DeleteLocationRequest;
use Amghost\Http\Requests\Api\Application\Locations\UpdateLocationRequest;

class LocationController extends ApplicationApiController
{
    /**
     * @var \Amghost\Services\Locations\LocationCreationService
     */
    private $creationService;

    /**
     * @var \Amghost\Services\Locations\LocationDeletionService
     */
    private $deletionService;

    /**
     * @var \Amghost\Contracts\Repository\LocationRepositoryInterface
     */
    private $repository;

    /**
     * @var \Amghost\Services\Locations\LocationUpdateService
     */
    private $updateService;

    /**
     * LocationController constructor.
     *
     * @param \Amghost\Services\Locations\LocationCreationService       $creationService
     * @param \Amghost\Services\Locations\LocationDeletionService       $deletionService
     * @param \Amghost\Contracts\Repository\LocationRepositoryInterface $repository
     * @param \Amghost\Services\Locations\LocationUpdateService         $updateService
     */
    public function __construct(
        LocationCreationService $creationService,
        LocationDeletionService $deletionService,
        LocationRepositoryInterface $repository,
        LocationUpdateService $updateService
    ) {
        parent::__construct();

        $this->creationService = $creationService;
        $this->deletionService = $deletionService;
        $this->repository = $repository;
        $this->updateService = $updateService;
    }

    /**
     * Return all of the locations currently registered on the Panel.
     *
     * @param \Amghost\Http\Requests\Api\Application\Locations\GetLocationsRequest $request
     * @return array
     */
    public function index(GetLocationsRequest $request): array
    {
        $locations = $this->repository->setSearchTerm($request->input('search'))->paginated(50);

        return $this->fractal->collection($locations)
            ->transformWith($this->getTransformer(LocationTransformer::class))
            ->toArray();
    }

    /**
     * Return a single location.
     *
     * @param \Amghost\Http\Requests\Api\Application\Locations\GetLocationRequest $request
     * @return array
     */
    public function view(GetLocationRequest $request): array
    {
        return $this->fractal->item($request->getModel(Location::class))
            ->transformWith($this->getTransformer(LocationTransformer::class))
            ->toArray();
    }

    /**
     * Store a new location on the Panel and return a HTTP/201 response code with the
     * new location attached.
     *
     * @param \Amghost\Http\Requests\Api\Application\Locations\StoreLocationRequest $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Amghost\Exceptions\Model\DataValidationException
     */
    public function store(StoreLocationRequest $request): JsonResponse
    {
        $location = $this->creationService->handle($request->validated());

        return $this->fractal->item($location)
            ->transformWith($this->getTransformer(LocationTransformer::class))
            ->addMeta([
                'resource' => route('api.application.locations.view', [
                    'location' => $location->id,
                ]),
            ])
            ->respond(201);
    }

    /**
     * Update a location on the Panel and return the updated record to the user.
     *
     * @param \Amghost\Http\Requests\Api\Application\Locations\UpdateLocationRequest $request
     * @return array
     *
     * @throws \Amghost\Exceptions\Model\DataValidationException
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function update(UpdateLocationRequest $request): array
    {
        $location = $this->updateService->handle($request->getModel(Location::class), $request->validated());

        return $this->fractal->item($location)
            ->transformWith($this->getTransformer(LocationTransformer::class))
            ->toArray();
    }

    /**
     * Delete a location from the Panel.
     *
     * @param \Amghost\Http\Requests\Api\Application\Locations\DeleteLocationRequest $request
     * @return \Illuminate\Http\Response
     *
     * @throws \Amghost\Exceptions\Service\Location\HasActiveNodesException
     */
    public function delete(DeleteLocationRequest $request): Response
    {
        $this->deletionService->handle($request->getModel(Location::class));

        return response('', 204);
    }
}
