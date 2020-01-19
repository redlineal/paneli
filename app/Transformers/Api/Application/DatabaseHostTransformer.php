<?php

namespace Amghost\Transformers\Api\Application;

use Cake\Chronos\Chronos;
use Amghost\Models\Database;
use Amghost\Models\DatabaseHost;
use Amghost\Services\Acl\Api\AdminAcl;

class DatabaseHostTransformer extends BaseTransformer
{
    /**
     * @var array
     */
    protected $availableIncludes = [
        'databases',
    ];

    /**
     * Return the resource name for the JSONAPI output.
     *
     * @return string
     */
    public function getResourceName(): string
    {
        return DatabaseHost::RESOURCE_NAME;
    }

    /**
     * Transform database host into a representation for the application API.
     *
     * @param \Amghost\Models\DatabaseHost $model
     * @return array
     */
    public function transform(DatabaseHost $model)
    {
        return [
            'id' => $model->id,
            'name' => $model->name,
            'host' => $model->host,
            'port' => $model->port,
            'username' => $model->username,
            'node' => $model->node_id,
            'created_at' => Chronos::createFromFormat(Chronos::DEFAULT_TO_STRING_FORMAT, $model->created_at)
                ->setTimezone(config('app.timezone'))
                ->toIso8601String(),
            'updated_at' => Chronos::createFromFormat(Chronos::DEFAULT_TO_STRING_FORMAT, $model->updated_at)
                ->setTimezone(config('app.timezone'))
                ->toIso8601String(),
        ];
    }

    /**
     * Include the databases associated with this host.
     *
     * @param \Amghost\Models\DatabaseHost $model
     * @return \League\Fractal\Resource\Collection|\League\Fractal\Resource\NullResource
     * @throws \Amghost\Exceptions\Transformer\InvalidTransformerLevelException
     */
    public function includeDatabases(DatabaseHost $model)
    {
        if (! $this->authorize(AdminAcl::RESOURCE_SERVER_DATABASES)) {
            return $this->null();
        }

        $model->loadMissing('databases');

        return $this->collection($model->getRelation('databases'), $this->makeTransformer(ServerDatabaseTransformer::class), Database::RESOURCE_NAME);
    }
}
