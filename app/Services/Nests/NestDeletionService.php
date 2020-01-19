<?php
/**
 * AMGHOST - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Services\Nests;

use Amghost\Contracts\Repository\NestRepositoryInterface;
use Amghost\Exceptions\Service\HasActiveServersException;
use Amghost\Contracts\Repository\ServerRepositoryInterface;

class NestDeletionService
{
    /**
     * @var \Amghost\Contracts\Repository\ServerRepositoryInterface
     */
    protected $serverRepository;

    /**
     * @var \Amghost\Contracts\Repository\NestRepositoryInterface
     */
    protected $repository;

    /**
     * NestDeletionService constructor.
     *
     * @param \Amghost\Contracts\Repository\ServerRepositoryInterface $serverRepository
     * @param \Amghost\Contracts\Repository\NestRepositoryInterface   $repository
     */
    public function __construct(
        ServerRepositoryInterface $serverRepository,
        NestRepositoryInterface $repository
    ) {
        $this->serverRepository = $serverRepository;
        $this->repository = $repository;
    }

    /**
     * Delete a nest from the system only if there are no servers attached to it.
     *
     * @param int $nest
     * @return int
     *
     * @throws \Amghost\Exceptions\Service\HasActiveServersException
     */
    public function handle(int $nest): int
    {
        $count = $this->serverRepository->findCountWhere([['nest_id', '=', $nest]]);
        if ($count > 0) {
            throw new HasActiveServersException(trans('exceptions.nest.delete_has_servers'));
        }

        return $this->repository->delete($nest);
    }
}
