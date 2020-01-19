<?php
/**
 * AMGHOST - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Services\Nests;

use Amghost\Contracts\Repository\NestRepositoryInterface;

class NestUpdateService
{
    /**
     * @var \Amghost\Contracts\Repository\NestRepositoryInterface
     */
    protected $repository;

    /**
     * NestUpdateService constructor.
     *
     * @param \Amghost\Contracts\Repository\NestRepositoryInterface $repository
     */
    public function __construct(NestRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Update a nest and prevent changing the author once it is set.
     *
     * @param int   $nest
     * @param array $data
     * @throws \Amghost\Exceptions\Model\DataValidationException
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function handle(int $nest, array $data)
    {
        if (! is_null(array_get($data, 'author'))) {
            unset($data['author']);
        }

        $this->repository->withoutFreshModel()->update($nest, $data);
    }
}
