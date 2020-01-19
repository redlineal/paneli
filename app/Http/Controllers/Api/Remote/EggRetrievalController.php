<?php
/**
 * AMGHOST - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Http\Controllers\Api\Remote;

use Illuminate\Http\JsonResponse;
use Amghost\Http\Controllers\Controller;
use Amghost\Services\Eggs\EggConfigurationService;
use Amghost\Contracts\Repository\EggRepositoryInterface;

class EggRetrievalController extends Controller
{
    /**
     * @var \Amghost\Services\Eggs\EggConfigurationService
     */
    protected $configurationFileService;

    /**
     * @var \Amghost\Contracts\Repository\EggRepositoryInterface
     */
    protected $repository;

    /**
     * OptionUpdateController constructor.
     *
     * @param \Amghost\Contracts\Repository\EggRepositoryInterface $repository
     * @param \Amghost\Services\Eggs\EggConfigurationService       $configurationFileService
     */
    public function __construct(
        EggRepositoryInterface $repository,
        EggConfigurationService $configurationFileService
    ) {
        $this->configurationFileService = $configurationFileService;
        $this->repository = $repository;
    }

    /**
     * Return a JSON array of Eggs and the SHA1 hash of their configuration file.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        $eggs = $this->repository->getAllWithCopyAttributes();

        $response = [];
        $eggs->each(function ($egg) use (&$response) {
            $response[$egg->uuid] = sha1(json_encode($this->configurationFileService->handle($egg)));
        });

        return response()->json($response);
    }

    /**
     * Return the configuration file for a single Egg for the Daemon.
     *
     * @param string $uuid
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function download(string $uuid): JsonResponse
    {
        $option = $this->repository->getWithCopyAttributes($uuid, 'uuid');

        return response()->json($this->configurationFileService->handle($option));
    }
}
