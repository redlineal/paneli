<?php
/**
 * AMGHOST - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Services\Packs;

use Amghost\Models\Pack;
use Amghost\Contracts\Repository\PackRepositoryInterface;
use Amghost\Exceptions\Service\HasActiveServersException;
use Amghost\Contracts\Repository\ServerRepositoryInterface;

class PackUpdateService
{
    /**
     * @var \Amghost\Contracts\Repository\PackRepositoryInterface
     */
    protected $repository;

    /**
     * @var \Amghost\Contracts\Repository\ServerRepositoryInterface
     */
    protected $serverRepository;

    /**
     * PackUpdateService constructor.
     *
     * @param \Amghost\Contracts\Repository\PackRepositoryInterface   $repository
     * @param \Amghost\Contracts\Repository\ServerRepositoryInterface $serverRepository
     */
    public function __construct(
        PackRepositoryInterface $repository,
        ServerRepositoryInterface $serverRepository
    ) {
        $this->repository = $repository;
        $this->serverRepository = $serverRepository;
    }

    /**
     * Update a pack.
     *
     * @param int|\Amghost\Models\Pack $pack
     * @param array                        $data
     * @return bool
     *
     * @throws \Amghost\Exceptions\Model\DataValidationException
     * @throws \Amghost\Exceptions\Service\HasActiveServersException
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function handle($pack, array $data)
    {
        if (! $pack instanceof Pack) {
            $pack = $this->repository->setColumns(['id', 'egg_id'])->find($pack);
        }

        if ((int) array_get($data, 'egg_id', $pack->egg_id) !== $pack->egg_id) {
            $count = $this->serverRepository->findCountWhere([['pack_id', '=', $pack->id]]);

            if ($count !== 0) {
                throw new HasActiveServersException(trans('exceptions.packs.update_has_servers'));
            }
        }

        // Transform values to boolean
        $data['selectable'] = isset($data['selectable']);
        $data['visible'] = isset($data['visible']);
        $data['locked'] = isset($data['locked']);

        return $this->repository->withoutFreshModel()->update($pack->id, $data);
    }
}
