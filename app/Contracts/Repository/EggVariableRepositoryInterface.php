<?php
/**
 * Amghost - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Contracts\Repository;

use Illuminate\Support\Collection;

interface EggVariableRepositoryInterface extends RepositoryInterface
{
    /**
     * Return editable variables for a given egg. Editable variables must be set to
     * user viewable in order to be picked up by this function.
     *
     * @param int $egg
     * @return \Illuminate\Support\Collection
     */
    public function getEditableVariables(int $egg): Collection;
}
