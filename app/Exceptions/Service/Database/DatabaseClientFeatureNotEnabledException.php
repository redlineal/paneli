<?php

namespace Amghost\Exceptions\Service\Database;

use Amghost\Exceptions\AmghostException;

class DatabaseClientFeatureNotEnabledException extends AmghostException
{
    public function __construct()
    {
        parent::__construct('Client database creation is not enabled in this Panel.');
    }
}
