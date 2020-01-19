<?php

namespace Amghost\Contracts\Repository;

use Amghost\Models\Subuser;

interface SubuserRepositoryInterface extends RepositoryInterface
{
    /**
     * Return a subuser with the associated server relationship.
     *
     * @param \Amghost\Models\Subuser $subuser
     * @param bool                        $refresh
     * @return \Amghost\Models\Subuser
     */
    public function loadServerAndUserRelations(Subuser $subuser, bool $refresh = false): Subuser;

    /**
     * Return a subuser with the associated permissions relationship.
     *
     * @param \Amghost\Models\Subuser $subuser
     * @param bool                        $refresh
     * @return \Amghost\Models\Subuser
     */
    public function getWithPermissions(Subuser $subuser, bool $refresh = false): Subuser;

    /**
     * Return a subuser and associated permissions given a user_id and server_id.
     *
     * @param int $user
     * @param int $server
     * @return \Amghost\Models\Subuser
     *
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function getWithPermissionsUsingUserAndServer(int $user, int $server): Subuser;
}
