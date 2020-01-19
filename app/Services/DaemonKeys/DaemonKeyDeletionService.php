<?php
/*
 * AMGHOST - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Amghost\Services\DaemonKeys;

use Webmozart\Assert\Assert;
use Amghost\Models\Server;
use Psr\Log\LoggerInterface as Writer;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Database\ConnectionInterface;
use Amghost\Exceptions\DisplayException;
use Amghost\Contracts\Repository\ServerRepositoryInterface;
use Amghost\Contracts\Repository\DaemonKeyRepositoryInterface;
use Amghost\Contracts\Repository\Daemon\ServerRepositoryInterface as DaemonServerRepositoryInterface;

class DaemonKeyDeletionService
{
    /**
     * @var \Illuminate\Database\ConnectionInterface
     */
    protected $connection;

    /**
     * @var \Amghost\Contracts\Repository\Daemon\ServerRepositoryInterface
     */
    protected $daemonRepository;

    /**
     * @var \Amghost\Contracts\Repository\DaemonKeyRepositoryInterface
     */
    protected $repository;

    /**
     * @var \Amghost\Contracts\Repository\ServerRepositoryInterface
     */
    protected $serverRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $writer;

    /**
     * DaemonKeyDeletionService constructor.
     *
     * @param \Illuminate\Database\ConnectionInterface                           $connection
     * @param \Amghost\Contracts\Repository\DaemonKeyRepositoryInterface     $repository
     * @param \Amghost\Contracts\Repository\Daemon\ServerRepositoryInterface $daemonRepository
     * @param \Amghost\Contracts\Repository\ServerRepositoryInterface        $serverRepository
     * @param \Psr\Log\LoggerInterface                                           $writer
     */
    public function __construct(
        ConnectionInterface $connection,
        DaemonKeyRepositoryInterface $repository,
        DaemonServerRepositoryInterface $daemonRepository,
        ServerRepositoryInterface $serverRepository,
        Writer $writer
    ) {
        $this->connection = $connection;
        $this->daemonRepository = $daemonRepository;
        $this->repository = $repository;
        $this->serverRepository = $serverRepository;
        $this->writer = $writer;
    }

    /**
     * @param \Amghost\Models\Server|int $server
     * @param int                            $user
     *
     * @throws \Amghost\Exceptions\DisplayException
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function handle($server, $user)
    {
        Assert::integerish($user, 'Second argument passed to handle must be an integer, received %s.');

        if (! $server instanceof Server) {
            $server = $this->serverRepository->find($server);
        }

        $this->connection->beginTransaction();
        $key = $this->repository->findFirstWhere([
            ['user_id', '=', $user],
            ['server_id', '=', $server->id],
        ]);

        $this->repository->delete($key->id);

        try {
            $this->daemonRepository->setServer($server)->revokeAccessKey($key->secret);
        } catch (RequestException $exception) {
            $response = $exception->getResponse();
            $this->connection->rollBack();
            $this->writer->warning($exception);

            throw new DisplayException(trans('admin/server.exceptions.daemon_exception', [
                'code' => is_null($response) ? 'E_CONN_REFUSED' : $response->getStatusCode(),
            ]));
        }

        $this->connection->commit();
    }
}
