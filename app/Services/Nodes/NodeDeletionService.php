<?php
/**
 * AMGHOST - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Services\Nodes;

use Amghost\Models\Node;
use Illuminate\Contracts\Translation\Translator;
use Amghost\Contracts\Repository\NodeRepositoryInterface;
use Amghost\Exceptions\Service\HasActiveServersException;
use Amghost\Contracts\Repository\ServerRepositoryInterface;

class NodeDeletionService
{
    /**
     * @var \Amghost\Contracts\Repository\NodeRepositoryInterface
     */
    protected $repository;

    /**
     * @var \Amghost\Contracts\Repository\ServerRepositoryInterface
     */
    protected $serverRepository;

    /**
     * @var \Illuminate\Contracts\Translation\Translator
     */
    protected $translator;

    /**
     * DeletionService constructor.
     *
     * @param \Amghost\Contracts\Repository\NodeRepositoryInterface   $repository
     * @param \Amghost\Contracts\Repository\ServerRepositoryInterface $serverRepository
     * @param \Illuminate\Contracts\Translation\Translator                $translator
     */
    public function __construct(
        NodeRepositoryInterface $repository,
        ServerRepositoryInterface $serverRepository,
        Translator $translator
    ) {
        $this->repository = $repository;
        $this->serverRepository = $serverRepository;
        $this->translator = $translator;
    }

    /**
     * Delete a node from the panel if no servers are attached to it.
     *
     * @param int|\Amghost\Models\Node $node
     * @return bool|null
     *
     * @throws \Amghost\Exceptions\Service\HasActiveServersException
     */
    public function handle($node)
    {
        if ($node instanceof Node) {
            $node = $node->id;
        }

        $servers = $this->serverRepository->setColumns('id')->findCountWhere([['node_id', '=', $node]]);
        if ($servers > 0) {
            throw new HasActiveServersException($this->translator->trans('exceptions.node.servers_attached'));
        }

        return $this->repository->delete($node);
    }
}
