<?php

namespace Amghost\Contracts\Core;

use Amghost\Events\Event;

interface ReceivesEvents
{
    /**
     * Handles receiving an event from the application.
     *
     * @param \Amghost\Events\Event $notification
     */
    public function handle(Event $notification): void;
}
