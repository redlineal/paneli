<?php
/**
 * AMGHOST - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Services\Databases\Hosts;

use Amghost\Models\DatabaseHost;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Contracts\Encryption\Encrypter;
use Amghost\Extensions\DynamicDatabaseConnection;
use Amghost\Contracts\Repository\DatabaseHostRepositoryInterface;

class HostUpdateService
{
    /**
     * @var \Illuminate\Database\ConnectionInterface
     */
    private $connection;

    /**
     * @var \Illuminate\Database\DatabaseManager
     */
    private $databaseManager;

    /**
     * @var \Amghost\Extensions\DynamicDatabaseConnection
     */
    private $dynamic;

    /**
     * @var \Illuminate\Contracts\Encryption\Encrypter
     */
    private $encrypter;

    /**
     * @var \Amghost\Contracts\Repository\DatabaseHostRepositoryInterface
     */
    private $repository;

    /**
     * DatabaseHostService constructor.
     *
     * @param \Illuminate\Database\ConnectionInterface                          $connection
     * @param \Illuminate\Database\DatabaseManager                              $databaseManager
     * @param \Amghost\Contracts\Repository\DatabaseHostRepositoryInterface $repository
     * @param \Amghost\Extensions\DynamicDatabaseConnection                 $dynamic
     * @param \Illuminate\Contracts\Encryption\Encrypter                        $encrypter
     */
    public function __construct(
        ConnectionInterface $connection,
        DatabaseManager $databaseManager,
        DatabaseHostRepositoryInterface $repository,
        DynamicDatabaseConnection $dynamic,
        Encrypter $encrypter
    ) {
        $this->connection = $connection;
        $this->databaseManager = $databaseManager;
        $this->dynamic = $dynamic;
        $this->encrypter = $encrypter;
        $this->repository = $repository;
    }

    /**
     * Update a database host and persist to the database.
     *
     * @param int   $hostId
     * @param array $data
     * @return \Amghost\Models\DatabaseHost
     *
     * @throws \Throwable
     */
    public function handle(int $hostId, array $data): DatabaseHost
    {
        if (! empty(array_get($data, 'password'))) {
            $data['password'] = $this->encrypter->encrypt($data['password']);
        } else {
            unset($data['password']);
        }

        return $this->connection->transaction(function () use ($data, $hostId) {
            $host = $this->repository->update($hostId, $data);
            $this->dynamic->set('dynamic', $host);
            $this->databaseManager->connection('dynamic')->select('SELECT 1 FROM dual');

            return $host;
        });
    }
}
