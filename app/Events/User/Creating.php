<?php
/**
 * Amghost - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Events\User;

use Amghost\Models\User;
use Illuminate\Queue\SerializesModels;

class Creating
{
    use SerializesModels;

    /**
     * The Eloquent model of the server.
     *
     * @var \Amghost\Models\User
     */
    public $user;

    /**
     * Create a new event instance.
     *
     * @param \Amghost\Models\User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
