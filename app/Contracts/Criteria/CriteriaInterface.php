<?php
/**
 * Amghost - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Contracts\Criteria;

use Amghost\Repositories\Repository;

interface CriteriaInterface
{
    /**
     * Apply selected criteria to a repository call.
     *
     * @param \Illuminate\Database\Eloquent\Model  $model
     * @param \Amghost\Repositories\Repository $repository
     * @return mixed
     */
    public function apply($model, Repository $repository);
}
