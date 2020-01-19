<?php
/**
 * AMGHOST - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Services\Servers;

use Amghost\Models\Server;
use Amghost\Contracts\Repository\ServerRepositoryInterface;

class ServerConfigurationStructureService
{
    const REQUIRED_RELATIONS = ['allocation', 'allocations', 'pack', 'option'];

    /**
     * @var \Amghost\Services\Servers\EnvironmentService
     */
    private $environment;

    /**
     * @var \Amghost\Contracts\Repository\ServerRepositoryInterface
     */
    private $repository;

    /**
     * ServerConfigurationStructureService constructor.
     *
     * @param \Amghost\Contracts\Repository\ServerRepositoryInterface $repository
     * @param \Amghost\Services\Servers\EnvironmentService            $environment
     */
    public function __construct(
        ServerRepositoryInterface $repository,
        EnvironmentService $environment
    ) {
        $this->repository = $repository;
        $this->environment = $environment;
    }

    /**
     * Return a configuration array for a specific server when passed a server model.
     *
     * @param \Amghost\Models\Server $server
     * @return array
     *
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function handle(Server $server): array
    {
        if (array_diff(self::REQUIRED_RELATIONS, $server->getRelations())) {
            $server = $this->repository->getDataForCreation($server);
        }

        $pack = $server->getRelation('pack');
        if (! is_null($pack)) {
            $pack = $server->getRelation('pack')->uuid;
        }

        return [
            'uuid' => $server->uuid,
            'build' => [
                'default' => [
                    'ip' => $server->allocation->ip,
                    'port' => $server->allocation->port,
                ],
                'ports' => $server->allocations->groupBy('ip')->map(function ($item) {
                    return $item->pluck('port');
                })->toArray(),
                'env' => $this->environment->handle($server),
                'oom_disabled' => $server->oom_disabled,
                'memory' => (int) $server->memory,
                'swap' => (int) $server->swap,
                'io' => (int) $server->io,
                'cpu' => (int) $server->cpu,
                'disk' => (int) $server->disk,
                'image' => $server->image,
            ],
            'service' => [
                'egg' => $server->egg->uuid,
                'pack' => $pack,
                'skip_scripts' => $server->skip_scripts,
            ],
            'rebuild' => false,
            'suspended' => (int) $server->suspended,
        ];
    }
}
