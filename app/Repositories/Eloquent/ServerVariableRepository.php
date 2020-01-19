<?php

namespace Amghost\Repositories\Eloquent;

use Amghost\Models\ServerVariable;
use Amghost\Contracts\Repository\ServerVariableRepositoryInterface;

class ServerVariableRepository extends EloquentRepository implements ServerVariableRepositoryInterface
{
    /**
     * Return the model backing this repository.
     *
     * @return string
     */
    public function model()
    {
        return ServerVariable::class;
    }
}
