<?php

namespace Amghost\Events\Server;

use Amghost\Events\Event;
use Amghost\Models\Server;
use Illuminate\Queue\SerializesModels;

class Installed extends Event
{
    use SerializesModels;

    /**
     * @var \Amghost\Models\Server
     */
    public $server;

    /**
     * Create a new event instance.
     *
     * @var \Amghost\Models\Server
     */
    public function __construct(Server $server)
    {
        $this->server = $server;
    }
}
