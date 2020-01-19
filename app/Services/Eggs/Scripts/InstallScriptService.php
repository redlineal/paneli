<?php
/**
 * AMGHOST - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Services\Eggs\Scripts;

use Amghost\Models\Egg;
use Amghost\Contracts\Repository\EggRepositoryInterface;
use Amghost\Exceptions\Service\Egg\InvalidCopyFromException;

class InstallScriptService
{
    /**
     * @var \Amghost\Contracts\Repository\EggRepositoryInterface
     */
    protected $repository;

    /**
     * InstallScriptService constructor.
     *
     * @param \Amghost\Contracts\Repository\EggRepositoryInterface $repository
     */
    public function __construct(EggRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Modify the install script for a given Egg.
     *
     * @param int|\Amghost\Models\Egg $egg
     * @param array                       $data
     *
     * @throws \Amghost\Exceptions\Model\DataValidationException
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     * @throws \Amghost\Exceptions\Service\Egg\InvalidCopyFromException
     */
    public function handle($egg, array $data)
    {
        if (! $egg instanceof Egg) {
            $egg = $this->repository->find($egg);
        }

        if (! is_null(array_get($data, 'copy_script_from'))) {
            if (! $this->repository->isCopyableScript(array_get($data, 'copy_script_from'), $egg->nest_id)) {
                throw new InvalidCopyFromException(trans('exceptions.nest.egg.invalid_copy_id'));
            }
        }

        $this->repository->withoutFreshModel()->update($egg->id, [
            'script_install' => array_get($data, 'script_install'),
            'script_is_privileged' => array_get($data, 'script_is_privileged', 1),
            'script_entry' => array_get($data, 'script_entry'),
            'script_container' => array_get($data, 'script_container'),
            'copy_script_from' => array_get($data, 'copy_script_from'),
        ]);
    }
}
