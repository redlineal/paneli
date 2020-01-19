<?php

namespace Amghost\Http\Controllers\Api\Application\Nests;

use Amghost\Models\Egg;
use Amghost\Models\Nest;
use Amghost\Contracts\Repository\EggRepositoryInterface;
use Amghost\Transformers\Api\Application\EggTransformer;
use Amghost\Http\Requests\Api\Application\Nests\Eggs\GetEggRequest;
use Amghost\Http\Requests\Api\Application\Nests\Eggs\GetEggsRequest;
use Amghost\Http\Controllers\Api\Application\ApplicationApiController;

class EggController extends ApplicationApiController
{
    /**
     * @var \Amghost\Contracts\Repository\EggRepositoryInterface
     */
    private $repository;

    /**
     * EggController constructor.
     *
     * @param \Amghost\Contracts\Repository\EggRepositoryInterface $repository
     */
    public function __construct(EggRepositoryInterface $repository)
    {
        parent::__construct();

        $this->repository = $repository;
    }

    /**
     * Return all eggs that exist for a given nest.
     *
     * @param \Amghost\Http\Requests\Api\Application\Nests\Eggs\GetEggsRequest $request
     * @return array
     */
    public function index(GetEggsRequest $request): array
    {
        $eggs = $this->repository->findWhere([
            ['nest_id', '=', $request->getModel(Nest::class)->id],
        ]);

        return $this->fractal->collection($eggs)
            ->transformWith($this->getTransformer(EggTransformer::class))
            ->toArray();
    }

    /**
     * Return a single egg that exists on the specified nest.
     *
     * @param \Amghost\Http\Requests\Api\Application\Nests\Eggs\GetEggRequest $request
     * @return array
     */
    public function view(GetEggRequest $request): array
    {
        return $this->fractal->item($request->getModel(Egg::class))
            ->transformWith($this->getTransformer(EggTransformer::class))
            ->toArray();
    }
}
