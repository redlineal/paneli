<?php
/**
 * AMGHOST - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Services\Nodes;

use Amghost\Contracts\Repository\NodeRepositoryInterface;

class NodeCreationService
{
    const DAEMON_SECRET_LENGTH = 36;

    /**
     * @var \Amghost\Contracts\Repository\NodeRepositoryInterface
     */
    protected $repository;

    /**
     * CreationService constructor.
     *
     * @param \Amghost\Contracts\Repository\NodeRepositoryInterface $repository
     */
    public function __construct(NodeRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Create a new node on the panel.
     *
     * @param array $data
     * @return \Amghost\Models\Node
     *
     * @throws \Amghost\Exceptions\Model\DataValidationException
     */
    public function handle(array $data)
    {
        $data['daemonSecret'] = str_random(self::DAEMON_SECRET_LENGTH);

        return $this->repository->create($data);
    }
}
