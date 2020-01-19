<?php
/**
 * AMGHOST - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Observers;

use Amghost\Events;
use Amghost\Models\Subuser;
use Amghost\Notifications\AddedToServer;
use Amghost\Notifications\RemovedFromServer;

class SubuserObserver
{
    /**
     * Listen to the Subuser creating event.
     *
     * @param \Amghost\Models\Subuser $subuser
     */
    public function creating(Subuser $subuser)
    {
        event(new Events\Subuser\Creating($subuser));
    }

    /**
     * Listen to the Subuser created event.
     *
     * @param \Amghost\Models\Subuser $subuser
     */
    public function created(Subuser $subuser)
    {
        event(new Events\Subuser\Created($subuser));

        $subuser->user->notify((new AddedToServer([
            'user' => $subuser->user->name_first,
            'name' => $subuser->server->name,
            'uuidShort' => $subuser->server->uuidShort,
        ])));
    }

    /**
     * Listen to the Subuser deleting event.
     *
     * @param \Amghost\Models\Subuser $subuser
     */
    public function deleting(Subuser $subuser)
    {
        event(new Events\Subuser\Deleting($subuser));
    }

    /**
     * Listen to the Subuser deleted event.
     *
     * @param \Amghost\Models\Subuser $subuser
     */
    public function deleted(Subuser $subuser)
    {
        event(new Events\Subuser\Deleted($subuser));

        $subuser->user->notify((new RemovedFromServer([
            'user' => $subuser->user->name_first,
            'name' => $subuser->server->name,
        ])));
    }
}
