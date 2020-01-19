<?php
/**
 * Amghost - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Events\Subuser;

use Amghost\Models\Subuser;
use Illuminate\Queue\SerializesModels;

class Deleted
{
    use SerializesModels;

    /**
     * The Eloquent model of the server.
     *
     * @var \Amghost\Models\Subuser
     */
    public $subuser;

    /**
     * Create a new event instance.
     *
     * @param \Amghost\Models\Subuser $subuser
     */
    public function __construct(Subuser $subuser)
    {
        $this->subuser = $subuser;
    }
}
