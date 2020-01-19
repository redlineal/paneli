<?php
/**
 * Amghost - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Contracts\Repository;

use Amghost\Models\Nest;

interface NestRepositoryInterface extends RepositoryInterface
{
    /**
     * Return a nest or all nests with their associated eggs, variables, and packs.
     *
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Collection|\Amghost\Models\Nest
     *
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function getWithEggs(int $id = null);

    /**
     * Return a nest or all nests and the count of eggs, packs, and servers for that nest.
     *
     * @param int|null $id
     * @return \Amghost\Models\Nest|\Illuminate\Database\Eloquent\Collection
     *
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function getWithCounts(int $id = null);

    /**
     * Return a nest along with its associated eggs and the servers relation on those eggs.
     *
     * @param int $id
     * @return \Amghost\Models\Nest
     *
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function getWithEggServers(int $id): Nest;
}
