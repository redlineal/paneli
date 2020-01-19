<?php
/**
 * AMGHOST - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Http\Controllers\Server\Files;

use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;
use Illuminate\Cache\Repository;
use Illuminate\Http\RedirectResponse;
use Amghost\Http\Controllers\Controller;

class DownloadController extends Controller
{
    /**
     * @var \Illuminate\Cache\Repository
     */
    protected $cache;

    /**
     * DownloadController constructor.
     *
     * @param \Illuminate\Cache\Repository $cache
     */
    public function __construct(Repository $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Setup a unique download link for a user to download a file from.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $uuid
     * @param string                   $file
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Request $request, string $uuid, string $file): RedirectResponse
    {
        $server = $request->attributes->get('server');
        $this->authorize('download-files', $server);

        $token = Uuid::uuid4()->toString();
        $node = $server->getRelation('node');

        $this->cache->put('Server:Downloads:' . $token, ['server' => $server->uuid, 'path' => $file], 5);

        return redirect(sprintf('%s://%s:%s/v1/server/file/download/%s', $node->scheme, $node->fqdn, $node->daemonListen, $token));
    }
}
