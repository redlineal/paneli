<?php

namespace Amghost\Transformers\Daemon;

use Carbon\Carbon;
use Amghost\Models\DaemonKey;
use Amghost\Models\Permission;
use League\Fractal\TransformerAbstract;
use Amghost\Contracts\Repository\SubuserRepositoryInterface;
use Amghost\Contracts\Repository\DaemonKeyRepositoryInterface;

class ApiKeyTransformer extends TransformerAbstract
{
    /**
     * @var \Amghost\Contracts\Repository\DaemonKeyRepositoryInterface
     */
    private $keyRepository;

    /**
     * @var \Amghost\Contracts\Repository\SubuserRepositoryInterface
     */
    private $repository;

    /**
     * ApiKeyTransformer constructor.
     *
     * @param \Amghost\Contracts\Repository\DaemonKeyRepositoryInterface $keyRepository
     * @param \Amghost\Contracts\Repository\SubuserRepositoryInterface   $repository
     */
    public function __construct(DaemonKeyRepositoryInterface $keyRepository, SubuserRepositoryInterface $repository)
    {
        $this->repository = $repository;
        $this->keyRepository = $keyRepository;
    }

    /**
     * Return a listing of servers that a daemon key can access.
     *
     * @param \Amghost\Models\DaemonKey $key
     * @return array
     *
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function transform(DaemonKey $key)
    {
        $this->keyRepository->loadServerAndUserRelations($key);

        if ($key->user_id === $key->getRelation('server')->owner_id || $key->getRelation('user')->root_admin) {
            return [
                'id' => $key->getRelation('server')->uuid,
                'is_temporary' => true,
                'expires_in' => max(Carbon::now()->diffInSeconds($key->expires_at, false), 0),
                'permissions' => ['s:*'],
            ];
        }

        $subuser = $this->repository->getWithPermissionsUsingUserAndServer($key->user_id, $key->server_id);

        $permissions = $subuser->getRelation('permissions')->pluck('permission')->toArray();
        $mappings = Permission::getPermissions(true);
        $daemonPermissions = ['s:console'];

        foreach ($permissions as $permission) {
            if (! is_null(array_get($mappings, $permission))) {
                $daemonPermissions[] = array_get($mappings, $permission);
            }
        }

        return [
            'id' => $key->getRelation('server')->uuid,
            'is_temporary' => true,
            'expires_in' => max(Carbon::now()->diffInSeconds($key->expires_at, false), 0),
            'permissions' => $daemonPermissions,
        ];
    }
}
