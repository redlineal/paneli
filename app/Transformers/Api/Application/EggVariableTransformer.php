<?php

namespace Amghost\Transformers\Api\Application;

use Amghost\Models\Egg;
use Amghost\Models\EggVariable;

class EggVariableTransformer extends BaseTransformer
{
    /**
     * Return the resource name for the JSONAPI output.
     *
     * @return string
     */
    public function getResourceName(): string
    {
        return Egg::RESOURCE_NAME;
    }

    public function transform(EggVariable $model)
    {
        return $model->toArray();
    }
}
