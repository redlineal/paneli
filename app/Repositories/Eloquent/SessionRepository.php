<?php

namespace Amghost\Repositories\Eloquent;

use Amghost\Models\Session;
use Illuminate\Support\Collection;
use Amghost\Contracts\Repository\SessionRepositoryInterface;

class SessionRepository extends EloquentRepository implements SessionRepositoryInterface
{
    /**
     * Return the model backing this repository.
     *
     * @return string
     */
    public function model()
    {
        return Session::class;
    }

    /**
     * Return all of the active sessions for a user.
     *
     * @param int $user
     * @return \Illuminate\Support\Collection
     */
    public function getUserSessions(int $user): Collection
    {
        return $this->getBuilder()->where('user_id', $user)->get($this->getColumns());
    }

    /**
     * Delete a session for a given user.
     *
     * @param int    $user
     * @param string $session
     * @return null|int
     */
    public function deleteUserSession(int $user, string $session)
    {
        return $this->getBuilder()->where('user_id', $user)->where('id', $session)->delete();
    }
}
