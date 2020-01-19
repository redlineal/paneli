<?php
/**
 * Amghost - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Events\Auth;

use Illuminate\Queue\SerializesModels;

class FailedCaptcha
{
    use SerializesModels;

    /**
     * The IP that the request originated from.
     *
     * @var string
     */
    public $ip;

    /**
     * The domain that was used to try to verify the request with recaptcha api.
     *
     * @var string
     */
    public $domain;

    /**
     * Create a new event instance.
     *
     * @param string $ip
     * @param string $domain
     */
    public function __construct($ip, $domain)
    {
        $this->ip = $ip;
        $this->domain = $domain;
    }
}
