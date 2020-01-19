<?php
/**
 * AMGHOST - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Services\Eggs\Sharing;

use Carbon\Carbon;
use Amghost\Contracts\Repository\EggRepositoryInterface;

class EggExporterService
{
    /**
     * @var \Amghost\Contracts\Repository\EggRepositoryInterface
     */
    protected $repository;

    /**
     * EggExporterService constructor.
     *
     * @param \Amghost\Contracts\Repository\EggRepositoryInterface $repository
     */
    public function __construct(EggRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Return a JSON representation of an egg and its variables.
     *
     * @param int $egg
     * @return string
     *
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function handle(int $egg): string
    {
        $egg = $this->repository->getWithExportAttributes($egg);

        $struct = [
            '_comment' => 'DO NOT EDIT: FILE GENERATED AUTOMATICALLY BY PTERODACTYL PANEL - PTERODACTYL.IO',
            'meta' => [
                'version' => 'PTDL_v1',
            ],
            'exported_at' => Carbon::now()->toIso8601String(),
            'name' => $egg->name,
            'author' => $egg->author,
            'description' => $egg->description,
            'image' => $egg->docker_image,
            'startup' => $egg->startup,
            'config' => [
                'files' => $egg->inherit_config_files,
                'startup' => $egg->inherit_config_startup,
                'logs' => $egg->inherit_config_logs,
                'stop' => $egg->inherit_config_stop,
            ],
            'scripts' => [
                'installation' => [
                    'script' => $egg->copy_script_install,
                    'container' => $egg->copy_script_container,
                    'entrypoint' => $egg->copy_script_entry,
                ],
            ],
            'variables' => $egg->variables->transform(function ($item) {
                return collect($item->toArray())->except([
                    'id', 'egg_id', 'created_at', 'updated_at',
                ])->toArray();
            }),
        ];

        return json_encode($struct, JSON_PRETTY_PRINT);
    }
}
