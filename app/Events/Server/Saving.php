<?php
/**
 * Amghost - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Events\Server;

use Amghost\Models\Server;
use Illuminate\Queue\SerializesModels;

class Saving
{
    use SerializesModels;

    /**
     * The Eloquent model of the server.
     *
     * @var \Amghost\Models\Server
     */
    public $server;

    /**
     * Create a new event instance.
     *
     * @param \Amghost\Models\Server $server
     */
    public function __construct(Server $server)
    {
        $this->server = $server;
    }
}
