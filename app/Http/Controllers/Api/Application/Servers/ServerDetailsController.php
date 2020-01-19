<?php

namespace Amghost\Http\Controllers\Api\Application\Servers;

use Amghost\Models\Server;
use Amghost\Services\Servers\BuildModificationService;
use Amghost\Services\Servers\DetailsModificationService;
use Amghost\Transformers\Api\Application\ServerTransformer;
use Amghost\Http\Controllers\Api\Application\ApplicationApiController;
use Amghost\Http\Requests\Api\Application\Servers\UpdateServerDetailsRequest;
use Amghost\Http\Requests\Api\Application\Servers\UpdateServerBuildConfigurationRequest;

class ServerDetailsController extends ApplicationApiController
{
    /**
     * @var \Amghost\Services\Servers\BuildModificationService
     */
    private $buildModificationService;

    /**
     * @var \Amghost\Services\Servers\DetailsModificationService
     */
    private $detailsModificationService;

    /**
     * ServerDetailsController constructor.
     *
     * @param \Amghost\Services\Servers\BuildModificationService   $buildModificationService
     * @param \Amghost\Services\Servers\DetailsModificationService $detailsModificationService
     */
    public function __construct(
        BuildModificationService $buildModificationService,
        DetailsModificationService $detailsModificationService
    ) {
        parent::__construct();

        $this->buildModificationService = $buildModificationService;
        $this->detailsModificationService = $detailsModificationService;
    }

    /**
     * Update the details for a specific server.
     *
     * @param \Amghost\Http\Requests\Api\Application\Servers\UpdateServerDetailsRequest $request
     * @return array
     *
     * @throws \Amghost\Exceptions\DisplayException
     * @throws \Amghost\Exceptions\Model\DataValidationException
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function details(UpdateServerDetailsRequest $request): array
    {
        $server = $this->detailsModificationService->returnUpdatedModel()->handle(
            $request->getModel(Server::class), $request->validated()
        );

        return $this->fractal->item($server)
            ->transformWith($this->getTransformer(ServerTransformer::class))
            ->toArray();
    }

    /**
     * Update the build details for a specific server.
     *
     * @param \Amghost\Http\Requests\Api\Application\Servers\UpdateServerBuildConfigurationRequest $request
     * @return array
     *
     * @throws \Amghost\Exceptions\DisplayException
     * @throws \Amghost\Exceptions\Model\DataValidationException
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function build(UpdateServerBuildConfigurationRequest $request): array
    {
        $server = $this->buildModificationService->handle($request->getModel(Server::class), $request->validated());

        return $this->fractal->item($server)
            ->transformWith($this->getTransformer(ServerTransformer::class))
            ->toArray();
    }
}
