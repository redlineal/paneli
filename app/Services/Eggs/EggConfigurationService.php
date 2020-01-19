<?php
/**
 * AMGHOST - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Services\Eggs;

use Amghost\Models\Egg;
use Amghost\Contracts\Repository\EggRepositoryInterface;

class EggConfigurationService
{
    /**
     * @var \Amghost\Contracts\Repository\EggRepositoryInterface
     */
    protected $repository;

    /**
     * EggConfigurationService constructor.
     *
     * @param \Amghost\Contracts\Repository\EggRepositoryInterface $repository
     */
    public function __construct(EggRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Return an Egg file to be used by the Daemon.
     *
     * @param int|\Amghost\Models\Egg $egg
     * @return array
     *
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function handle($egg): array
    {
        if (! $egg instanceof Egg) {
            $egg = $this->repository->getWithCopyAttributes($egg);
        }

        return [
            'startup' => json_decode($egg->inherit_config_startup),
            'stop' => $egg->inherit_config_stop,
            'configs' => json_decode($egg->inherit_config_files),
            'log' => json_decode($egg->inherit_config_logs),
            'query' => 'none',
        ];
    }
}
