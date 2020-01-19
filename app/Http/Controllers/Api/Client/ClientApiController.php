<?php

namespace Amghost\Http\Controllers\Api\Client;

use Webmozart\Assert\Assert;
use Illuminate\Container\Container;
use Amghost\Transformers\Api\Client\BaseClientTransformer;
use Amghost\Http\Controllers\Api\Application\ApplicationApiController;

abstract class ClientApiController extends ApplicationApiController
{
    /**
     * Return an instance of an application transformer.
     *
     * @param string $abstract
     * @return \Amghost\Transformers\Api\Client\BaseClientTransformer
     */
    public function getTransformer(string $abstract)
    {
        /** @var \Amghost\Transformers\Api\Client\BaseClientTransformer $transformer */
        $transformer = Container::getInstance()->make($abstract);
        Assert::isInstanceOf($transformer, BaseClientTransformer::class);

        $transformer->setKey($this->request->attributes->get('api_key'));
        $transformer->setUser($this->request->user());

        return $transformer;
    }
}
