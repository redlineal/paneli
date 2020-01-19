<?php
/**
 * AMGHOST - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Services\Packs;

use Amghost\Models\Pack;
use Illuminate\Database\ConnectionInterface;
use Amghost\Contracts\Repository\PackRepositoryInterface;
use Amghost\Exceptions\Service\HasActiveServersException;
use Amghost\Contracts\Repository\ServerRepositoryInterface;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;

class PackDeletionService
{
    /**
     * @var \Illuminate\Database\ConnectionInterface
     */
    protected $connection;

    /**
     * @var \Amghost\Contracts\Repository\PackRepositoryInterface
     */
    protected $repository;

    /**
     * @var \Amghost\Contracts\Repository\ServerRepositoryInterface
     */
    protected $serverRepository;

    /**
     * @var \Illuminate\Contracts\Filesystem\Factory
     */
    protected $storage;

    /**
     * PackDeletionService constructor.
     *
     * @param \Illuminate\Database\ConnectionInterface                    $connection
     * @param \Illuminate\Contracts\Filesystem\Factory                    $storage
     * @param \Amghost\Contracts\Repository\PackRepositoryInterface   $repository
     * @param \Amghost\Contracts\Repository\ServerRepositoryInterface $serverRepository
     */
    public function __construct(
        ConnectionInterface $connection,
        FilesystemFactory $storage,
        PackRepositoryInterface $repository,
        ServerRepositoryInterface $serverRepository
    ) {
        $this->connection = $connection;
        $this->repository = $repository;
        $this->serverRepository = $serverRepository;
        $this->storage = $storage;
    }

    /**
     * Delete a pack from the database as well as the archive stored on the server.
     *
     * @param  int|\Amghost\Models\Pack$pack
     *
     * @throws \Amghost\Exceptions\Service\HasActiveServersException
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function handle($pack)
    {
        if (! $pack instanceof Pack) {
            $pack = $this->repository->setColumns(['id', 'uuid'])->find($pack);
        }

        $count = $this->serverRepository->findCountWhere([['pack_id', '=', $pack->id]]);
        if ($count !== 0) {
            throw new HasActiveServersException(trans('exceptions.packs.delete_has_servers'));
        }

        $this->connection->beginTransaction();
        $this->repository->delete($pack->id);
        $this->storage->disk()->deleteDirectory('packs/' . $pack->uuid);
        $this->connection->commit();
    }
}
