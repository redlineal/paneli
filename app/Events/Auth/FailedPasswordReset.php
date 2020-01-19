<?php
/**
 * Amghost - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Events\Auth;

use Illuminate\Queue\SerializesModels;

class FailedPasswordReset
{
    use SerializesModels;

    /**
     * The IP that the request originated from.
     *
     * @var string
     */
    public $ip;

    /**
     * The email address that was used when the reset request failed.
     *
     * @var string
     */
    public $email;

    /**
     * Create a new event instance.
     *
     * @param string $ip
     * @param string $email
     */
    public function __construct($ip, $email)
    {
        $this->ip = $ip;
        $this->email = $email;
    }
}
