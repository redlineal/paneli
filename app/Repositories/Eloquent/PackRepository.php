<?php

namespace Amghost\Repositories\Eloquent;

use Amghost\Models\Pack;
use Amghost\Repositories\Concerns\Searchable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Amghost\Contracts\Repository\PackRepositoryInterface;

class PackRepository extends EloquentRepository implements PackRepositoryInterface
{
    use Searchable;

    /**
     * Return the model backing this repository.
     *
     * @return string
     */
    public function model()
    {
        return Pack::class;
    }

    /**
     * Return a pack with the associated server models attached to it.
     *
     * @param \Amghost\Models\Pack $pack
     * @param bool                     $refresh
     * @return \Amghost\Models\Pack
     */
    public function loadServerData(Pack $pack, bool $refresh = false): Pack
    {
        if ($refresh) {
            $pack->load(['servers.node', 'servers.user']);
        }

        $pack->loadMissing(['servers.node', 'servers.user']);

        return $pack;
    }

    /**
     * Return a paginated listing of packs with their associated egg and server count.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginateWithEggAndServerCount(): LengthAwarePaginator
    {
        return $this->getBuilder()->with('egg')->withCount('servers')
            ->search($this->getSearchTerm())
            ->paginate(50, $this->getColumns());
    }
}
