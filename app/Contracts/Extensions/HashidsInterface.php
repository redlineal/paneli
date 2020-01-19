<?php
/**
 * Amghost - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Contracts\Extensions;

use Hashids\HashidsInterface as VendorHashidsInterface;

interface HashidsInterface extends VendorHashidsInterface
{
    /**
     * Decode an encoded hashid and return the first result.
     *
     * @param string $encoded
     * @param null   $default
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    public function decodeFirst($encoded, $default = null);
}
