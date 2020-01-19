<?php

namespace Amghost\Http\Controllers\Api\Application\Nests;

use Amghost\Models\Nest;
use Amghost\Contracts\Repository\NestRepositoryInterface;
use Amghost\Transformers\Api\Application\NestTransformer;
use Amghost\Http\Requests\Api\Application\Nests\GetNestsRequest;
use Amghost\Http\Controllers\Api\Application\ApplicationApiController;

class NestController extends ApplicationApiController
{
    /**
     * @var \Amghost\Contracts\Repository\NestRepositoryInterface
     */
    private $repository;

    /**
     * NestController constructor.
     *
     * @param \Amghost\Contracts\Repository\NestRepositoryInterface $repository
     */
    public function __construct(NestRepositoryInterface $repository)
    {
        parent::__construct();

        $this->repository = $repository;
    }

    /**
     * Return all Nests that exist on the Panel.
     *
     * @param \Amghost\Http\Requests\Api\Application\Nests\GetNestsRequest $request
     * @return array
     */
    public function index(GetNestsRequest $request): array
    {
        $nests = $this->repository->paginated(50);

        return $this->fractal->collection($nests)
            ->transformWith($this->getTransformer(NestTransformer::class))
            ->toArray();
    }

    /**
     * Return information about a single Nest model.
     *
     * @param \Amghost\Http\Requests\Api\Application\Nests\GetNestsRequest $request
     * @return array
     */
    public function view(GetNestsRequest $request): array
    {
        return $this->fractal->item($request->getModel(Nest::class))
            ->transformWith($this->getTransformer(NestTransformer::class))
            ->toArray();
    }
}
