<?php

namespace Amghost\Services\Servers;

use Amghost\Models\User;
use Amghost\Models\Server;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Database\ConnectionInterface;
use Amghost\Traits\Services\HasUserLevels;
use Amghost\Contracts\Repository\EggRepositoryInterface;
use Amghost\Contracts\Repository\ServerRepositoryInterface;
use Amghost\Exceptions\Http\Connection\DaemonConnectionException;
use Amghost\Contracts\Repository\ServerVariableRepositoryInterface;
use Amghost\Contracts\Repository\Daemon\ServerRepositoryInterface as DaemonServerRepositoryInterface;

class StartupModificationService
{
    use HasUserLevels;

    /**
     * @var \Amghost\Contracts\Repository\Daemon\ServerRepositoryInterface
     */
    private $daemonServerRepository;

    /**
     * @var \Illuminate\Database\ConnectionInterface
     */
    private $connection;

    /**
     * @var \Amghost\Contracts\Repository\EggRepositoryInterface
     */
    private $eggRepository;

    /**
     * @var \Amghost\Services\Servers\EnvironmentService
     */
    private $environmentService;

    /**
     * @var \Amghost\Contracts\Repository\ServerRepositoryInterface
     */
    private $repository;

    /**
     * @var \Amghost\Contracts\Repository\ServerVariableRepositoryInterface
     */
    private $serverVariableRepository;

    /**
     * @var \Amghost\Services\Servers\VariableValidatorService
     */
    private $validatorService;

    /**
     * StartupModificationService constructor.
     *
     * @param \Illuminate\Database\ConnectionInterface                            $connection
     * @param \Amghost\Contracts\Repository\Daemon\ServerRepositoryInterface  $daemonServerRepository
     * @param \Amghost\Contracts\Repository\EggRepositoryInterface            $eggRepository
     * @param \Amghost\Services\Servers\EnvironmentService                    $environmentService
     * @param \Amghost\Contracts\Repository\ServerRepositoryInterface         $repository
     * @param \Amghost\Contracts\Repository\ServerVariableRepositoryInterface $serverVariableRepository
     * @param \Amghost\Services\Servers\VariableValidatorService              $validatorService
     */
    public function __construct(
        ConnectionInterface $connection,
        DaemonServerRepositoryInterface $daemonServerRepository,
        EggRepositoryInterface $eggRepository,
        EnvironmentService $environmentService,
        ServerRepositoryInterface $repository,
        ServerVariableRepositoryInterface $serverVariableRepository,
        VariableValidatorService $validatorService
    ) {
        $this->daemonServerRepository = $daemonServerRepository;
        $this->connection = $connection;
        $this->eggRepository = $eggRepository;
        $this->environmentService = $environmentService;
        $this->repository = $repository;
        $this->serverVariableRepository = $serverVariableRepository;
        $this->validatorService = $validatorService;
    }

    /**
     * Process startup modification for a server.
     *
     * @param \Amghost\Models\Server $server
     * @param array                      $data
     * @return \Amghost\Models\Server
     *
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Amghost\Exceptions\Http\Connection\DaemonConnectionException
     * @throws \Amghost\Exceptions\Model\DataValidationException
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function handle(Server $server, array $data): Server
    {
        $this->connection->beginTransaction();
        if (! is_null(array_get($data, 'environment'))) {
            $this->validatorService->setUserLevel($this->getUserLevel());
            $results = $this->validatorService->handle(array_get($data, 'egg_id', $server->egg_id), array_get($data, 'environment', []));

            $results->each(function ($result) use ($server) {
                $this->serverVariableRepository->withoutFreshModel()->updateOrCreate([
                    'server_id' => $server->id,
                    'variable_id' => $result->id,
                ], [
                    'variable_value' => $result->value ?? '',
                ]);
            });
        }

        $daemonData = [];
        if ($this->isUserLevel(User::USER_LEVEL_ADMIN)) {
            $this->updateAdministrativeSettings($data, $server, $daemonData);
        }

        $daemonData = array_merge_recursive($daemonData, [
            'build' => [
                'env|overwrite' => $this->environmentService->handle($server),
            ],
        ]);

        try {
            $this->daemonServerRepository->setServer($server)->update($daemonData);
        } catch (RequestException $exception) {
            $this->connection->rollBack();
            throw new DaemonConnectionException($exception);
        }

        $this->connection->commit();

        return $server;
    }

    /**
     * Update certain administrative settings for a server in the DB.
     *
     * @param array                      $data
     * @param \Amghost\Models\Server $server
     * @param array                      $daemonData
     *
     * @throws \Amghost\Exceptions\Model\DataValidationException
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    private function updateAdministrativeSettings(array $data, Server &$server, array &$daemonData)
    {
        if (
            is_digit(array_get($data, 'egg_id'))
            && $data['egg_id'] != $server->egg_id
            && is_null(array_get($data, 'nest_id'))
        ) {
            $egg = $this->eggRepository->setColumns(['id', 'nest_id'])->find($data['egg_id']);
            $data['nest_id'] = $egg->nest_id;
        }

        $server = $this->repository->update($server->id, [
            'installed' => 0,
            'startup' => array_get($data, 'startup', $server->startup),
            'nest_id' => array_get($data, 'nest_id', $server->nest_id),
            'egg_id' => array_get($data, 'egg_id', $server->egg_id),
            'pack_id' => array_get($data, 'pack_id', $server->pack_id) > 0 ? array_get($data, 'pack_id', $server->pack_id) : null,
            'skip_scripts' => array_get($data, 'skip_scripts') ?? isset($data['skip_scripts']),
            'image' => array_get($data, 'docker_image', $server->image),
        ]);

        $daemonData = array_merge($daemonData, [
            'build' => ['image' => $server->image],
            'service' => array_merge(
                $this->repository->getDaemonServiceData($server, true),
                ['skip_scripts' => $server->skip_scripts]
            ),
        ]);
    }
}
