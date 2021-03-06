<?php
/**
 * Amghost - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Extensions;

use Amghost\Models\DatabaseHost;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Config\Repository as ConfigRepository;
use Amghost\Contracts\Repository\DatabaseHostRepositoryInterface;

class DynamicDatabaseConnection
{
    const DB_CHARSET = 'utf8';
    const DB_COLLATION = 'utf8_unicode_ci';
    const DB_DRIVER = 'mysql';

    /**
     * @var \Illuminate\Config\Repository
     */
    protected $config;

    /**
     * @var \Illuminate\Contracts\Encryption\Encrypter
     */
    protected $encrypter;

    /**
     * @var \Amghost\Contracts\Repository\DatabaseHostRepositoryInterface
     */
    protected $repository;

    /**
     * DynamicDatabaseConnection constructor.
     *
     * @param \Illuminate\Config\Repository                                     $config
     * @param \Amghost\Contracts\Repository\DatabaseHostRepositoryInterface $repository
     * @param \Illuminate\Contracts\Encryption\Encrypter                        $encrypter
     */
    public function __construct(
        ConfigRepository $config,
        DatabaseHostRepositoryInterface $repository,
        Encrypter $encrypter
    ) {
        $this->config = $config;
        $this->encrypter = $encrypter;
        $this->repository = $repository;
    }

    /**
     * Adds a dynamic database connection entry to the runtime config.
     *
     * @param string                               $connection
     * @param \Amghost\Models\DatabaseHost|int $host
     * @param string                               $database
     *
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function set($connection, $host, $database = 'mysql')
    {
        if (! $host instanceof DatabaseHost) {
            $host = $this->repository->find($host);
        }

        $this->config->set('database.connections.' . $connection, [
            'driver' => self::DB_DRIVER,
            'host' => $host->host,
            'port' => $host->port,
            'database' => $database,
            'username' => $host->username,
            'password' => $this->encrypter->decrypt($host->password),
            'charset' => self::DB_CHARSET,
            'collation' => self::DB_COLLATION,
        ]);
    }
}
