<?php

namespace Amghost\Contracts\Repository\Daemon;

use GuzzleHttp\Client;
use Amghost\Models\Node;
use Amghost\Models\Server;

interface BaseRepositoryInterface
{
    /**
     * Set the node model to be used for this daemon connection.
     *
     * @param \Amghost\Models\Node $node
     * @return $this
     */
    public function setNode(Node $node);

    /**
     * Return the node model being used.
     *
     * @return \Amghost\Models\Node|null
     */
    public function getNode();

    /**
     * Set the Server model to use when requesting information from the Daemon.
     *
     * @param \Amghost\Models\Server $server
     * @return $this
     */
    public function setServer(Server $server);

    /**
     * Return the Server model.
     *
     * @return \Amghost\Models\Server|null
     */
    public function getServer();

    /**
     * Set the token to be used in the X-Access-Token header for requests to the daemon.
     *
     * @param string $token
     * @return $this
     */
    public function setToken(string $token);

    /**
     * Return the access token being used for requests.
     *
     * @return string|null
     */
    public function getToken();

    /**
     * Return an instance of the Guzzle HTTP Client to be used for requests.
     *
     * @param array $headers
     * @return \GuzzleHttp\Client
     *
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function getHttpClient(array $headers = []): Client;
}
