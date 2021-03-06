<?php

namespace Amghost\Services\Databases\Hosts;

use Amghost\Models\DatabaseHost;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Contracts\Encryption\Encrypter;
use Amghost\Extensions\DynamicDatabaseConnection;
use Amghost\Contracts\Repository\DatabaseHostRepositoryInterface;

class HostCreationService
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
     * HostCreationService constructor.
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
     * Create a new database host on the Panel.
     *
     * @param array $data
     * @return \Amghost\Models\DatabaseHost
     *
     * @throws \Throwable
     */
    public function handle(array $data): DatabaseHost
    {
        return $this->connection->transaction(function () use ($data) {
            $host = $this->repository->create([
                'password' => $this->encrypter->encrypt(array_get($data, 'password')),
                'name' => array_get($data, 'name'),
                'host' => array_get($data, 'host'),
                'port' => array_get($data, 'port'),
                'username' => array_get($data, 'username'),
                'max_databases' => null,
                'node_id' => array_get($data, 'node_id'),
            ]);

            // Confirm access using the provided credentials before saving data.
            $this->dynamic->set('dynamic', $host);
            $this->databaseManager->connection('dynamic')->select('SELECT 1 FROM dual');

            return $host;
        });
    }
}
