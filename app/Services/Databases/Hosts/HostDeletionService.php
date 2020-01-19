<?php

namespace Amghost\Services\Databases\Hosts;

use Amghost\Exceptions\Service\HasActiveServersException;
use Amghost\Contracts\Repository\DatabaseRepositoryInterface;
use Amghost\Contracts\Repository\DatabaseHostRepositoryInterface;

class HostDeletionService
{
    /**
     * @var \Amghost\Contracts\Repository\DatabaseRepositoryInterface
     */
    private $databaseRepository;

    /**
     * @var \Amghost\Contracts\Repository\DatabaseHostRepositoryInterface
     */
    private $repository;

    /**
     * HostDeletionService constructor.
     *
     * @param \Amghost\Contracts\Repository\DatabaseRepositoryInterface     $databaseRepository
     * @param \Amghost\Contracts\Repository\DatabaseHostRepositoryInterface $repository
     */
    public function __construct(
        DatabaseRepositoryInterface $databaseRepository,
        DatabaseHostRepositoryInterface $repository
    ) {
        $this->databaseRepository = $databaseRepository;
        $this->repository = $repository;
    }

    /**
     * Delete a specified host from the Panel if no databases are
     * attached to it.
     *
     * @param int $host
     * @return int
     *
     * @throws \Amghost\Exceptions\Service\HasActiveServersException
     */
    public function handle(int $host): int
    {
        $count = $this->databaseRepository->findCountWhere([['database_host_id', '=', $host]]);
        if ($count > 0) {
            throw new HasActiveServersException(trans('exceptions.databases.delete_has_databases'));
        }

        return $this->repository->delete($host);
    }
}
