<?php
/**
 * AMGHOST - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Repositories\Eloquent;

use Amghost\Models\Nest;
use Amghost\Contracts\Repository\NestRepositoryInterface;
use Amghost\Exceptions\Repository\RecordNotFoundException;

class NestRepository extends EloquentRepository implements NestRepositoryInterface
{
    /**
     * Return the model backing this repository.
     *
     * @return string
     */
    public function model()
    {
        return Nest::class;
    }

    /**
     * Return a nest or all nests with their associated eggs, variables, and packs.
     *
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Collection|\Amghost\Models\Nest
     *
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function getWithEggs(int $id = null)
    {
        $instance = $this->getBuilder()->with('eggs.packs', 'eggs.variables');

        if (! is_null($id)) {
            $instance = $instance->find($id, $this->getColumns());
            if (! $instance) {
                throw new RecordNotFoundException;
            }

            return $instance;
        }

        return $instance->get($this->getColumns());
    }

    /**
     * Return a nest or all nests and the count of eggs, packs, and servers for that nest.
     *
     * @param int|null $id
     * @return \Amghost\Models\Nest|\Illuminate\Database\Eloquent\Collection
     *
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function getWithCounts(int $id = null)
    {
        $instance = $this->getBuilder()->withCount(['eggs', 'packs', 'servers']);

        if (! is_null($id)) {
            $instance = $instance->find($id, $this->getColumns());
            if (! $instance) {
                throw new RecordNotFoundException;
            }

            return $instance;
        }

        return $instance->get($this->getColumns());
    }

    /**
     * Return a nest along with its associated eggs and the servers relation on those eggs.
     *
     * @param int $id
     * @return \Amghost\Models\Nest
     *
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function getWithEggServers(int $id): Nest
    {
        $instance = $this->getBuilder()->with('eggs.servers')->find($id, $this->getColumns());
        if (! $instance) {
            throw new RecordNotFoundException;
        }

        /* @var Nest $instance */
        return $instance;
    }
}
