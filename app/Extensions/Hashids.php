<?php
/**
 * Amghost - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Extensions;

use Hashids\Hashids as VendorHashids;
use Amghost\Contracts\Extensions\HashidsInterface;

class Hashids extends VendorHashids implements HashidsInterface
{
    /**
     * {@inheritdoc}
     */
    public function decodeFirst($encoded, $default = null)
    {
        $result = $this->decode($encoded);
        if (! is_array($result)) {
            return $default;
        }

        return array_first($result, null, $default);
    }
}
