<?php

namespace Amghost\Repositories\Eloquent;

use Amghost\Models\Subuser;
use Amghost\Exceptions\Repository\RecordNotFoundException;
use Amghost\Contracts\Repository\SubuserRepositoryInterface;

class SubuserRepository extends EloquentRepository implements SubuserRepositoryInterface
{
    /**
     * Return the model backing this repository.
     *
     * @return string
     */
    public function model()
    {
        return Subuser::class;
    }

    /**
     * Return a subuser with the associated server relationship.
     *
     * @param \Amghost\Models\Subuser $subuser
     * @param bool                        $refresh
     * @return \Amghost\Models\Subuser
     */
    public function loadServerAndUserRelations(Subuser $subuser, bool $refresh = false): Subuser
    {
        if (! $subuser->relationLoaded('server') || $refresh) {
            $subuser->load('server');
        }

        if (! $subuser->relationLoaded('user') || $refresh) {
            $subuser->load('user');
        }

        return $subuser;
    }

    /**
     * Return a subuser with the associated permissions relationship.
     *
     * @param \Amghost\Models\Subuser $subuser
     * @param bool                        $refresh
     * @return \Amghost\Models\Subuser
     */
    public function getWithPermissions(Subuser $subuser, bool $refresh = false): Subuser
    {
        if (! $subuser->relationLoaded('permissions') || $refresh) {
            $subuser->load('permissions');
        }

        if (! $subuser->relationLoaded('user') || $refresh) {
            $subuser->load('user');
        }

        return $subuser;
    }

    /**
     * Return a subuser and associated permissions given a user_id and server_id.
     *
     * @param int $user
     * @param int $server
     * @return \Amghost\Models\Subuser
     *
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function getWithPermissionsUsingUserAndServer(int $user, int $server): Subuser
    {
        $instance = $this->getBuilder()->with('permissions')->where([
            ['user_id', '=', $user],
            ['server_id', '=', $server],
        ])->first();

        if (is_null($instance)) {
            throw new RecordNotFoundException;
        }

        return $instance;
    }
}
