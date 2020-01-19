<?php

namespace Amghost\Http\Requests\Api\Application\Servers\Databases;

use Amghost\Services\Acl\Api\AdminAcl;

class ServerDatabaseWriteRequest extends GetServerDatabasesRequest
{
    /**
     * @var int
     */
    protected $permission = AdminAcl::WRITE;
}
