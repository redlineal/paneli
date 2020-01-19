<?php

namespace Amghost\Repositories\Eloquent;

use Illuminate\Support\Collection;
use Amghost\Models\EggVariable;
use Amghost\Contracts\Repository\EggVariableRepositoryInterface;

class EggVariableRepository extends EloquentRepository implements EggVariableRepositoryInterface
{
    /**
     * Return the model backing this repository.
     *
     * @return string
     */
    public function model()
    {
        return EggVariable::class;
    }

    /**
     * Return editable variables for a given egg. Editable variables must be set to
     * user viewable in order to be picked up by this function.
     *
     * @param int $egg
     * @return \Illuminate\Support\Collection
     */
    public function getEditableVariables(int $egg): Collection
    {
        return $this->getBuilder()->where([
            ['egg_id', '=', $egg],
            ['user_viewable', '=', 1],
            ['user_editable', '=', 1],
        ])->get($this->getColumns());
    }
}
