<?php
/**
 * AMGHOST - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Services\Users;

use Amghost\Models\User;
use Amghost\Exceptions\DisplayException;
use Illuminate\Contracts\Translation\Translator;
use Amghost\Contracts\Repository\UserRepositoryInterface;
use Amghost\Contracts\Repository\ServerRepositoryInterface;

class UserDeletionService
{
    /**
     * @var \Amghost\Contracts\Repository\UserRepositoryInterface
     */
    protected $repository;

    /**
     * @var \Illuminate\Contracts\Translation\Translator
     */
    protected $translator;

    /**
     * @var \Amghost\Contracts\Repository\ServerRepositoryInterface
     */
    protected $serverRepository;

    /**
     * DeletionService constructor.
     *
     * @param \Amghost\Contracts\Repository\ServerRepositoryInterface $serverRepository
     * @param \Illuminate\Contracts\Translation\Translator                $translator
     * @param \Amghost\Contracts\Repository\UserRepositoryInterface   $repository
     */
    public function __construct(
        ServerRepositoryInterface $serverRepository,
        Translator $translator,
        UserRepositoryInterface $repository
    ) {
        $this->repository = $repository;
        $this->translator = $translator;
        $this->serverRepository = $serverRepository;
    }

    /**
     * Delete a user from the panel only if they have no servers attached to their account.
     *
     * @param int|\Amghost\Models\User $user
     * @return bool|null
     *
     * @throws \Amghost\Exceptions\DisplayException
     */
    public function handle($user)
    {
        if ($user instanceof User) {
            $user = $user->id;
        }

        $servers = $this->serverRepository->setColumns('id')->findCountWhere([['owner_id', '=', $user]]);
        if ($servers > 0) {
            throw new DisplayException($this->translator->trans('admin/user.exceptions.user_has_servers'));
        }

        return $this->repository->delete($user);
    }
}
