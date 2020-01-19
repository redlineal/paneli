<?php

namespace Amghost\Http\Requests\Api\Application\Servers\Databases;

use Amghost\Services\Acl\Api\AdminAcl;
use Amghost\Http\Requests\Api\Application\ApplicationApiRequest;

class GetServerDatabaseRequest extends ApplicationApiRequest
{
    /**
     * @var string
     */
    protected $resource = AdminAcl::RESOURCE_SERVER_DATABASES;

    /**
     * @var int
     */
    protected $permission = AdminAcl::READ;

    /**
     * Determine if the requested server database exists.
     *
     * @return bool
     */
    public function resourceExists(): bool
    {
        $server = $this->route()->parameter('server');
        $database = $this->route()->parameter('database');

        return $database->server_id === $server->id;
    }
}
