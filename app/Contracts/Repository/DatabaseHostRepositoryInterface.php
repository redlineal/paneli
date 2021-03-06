<?php

namespace Amghost\Contracts\Repository;

use Illuminate\Support\Collection;

interface DatabaseHostRepositoryInterface extends RepositoryInterface
{
    /**
     * Return database hosts with a count of databases and the node
     * information for which it is attached.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getWithViewDetails(): Collection;
}
