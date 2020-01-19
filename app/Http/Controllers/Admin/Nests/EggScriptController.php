<?php
/**
 * Amghost - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Http\Controllers\Admin\Nests;

use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Amghost\Http\Controllers\Controller;
use Amghost\Services\Eggs\Scripts\InstallScriptService;
use Amghost\Contracts\Repository\EggRepositoryInterface;
use Amghost\Http\Requests\Admin\Egg\EggScriptFormRequest;

class EggScriptController extends Controller
{
    /**
     * @var \Prologue\Alerts\AlertsMessageBag
     */
    protected $alert;

    /**
     * @var \Amghost\Services\Eggs\Scripts\InstallScriptService
     */
    protected $installScriptService;

    /**
     * @var \Amghost\Contracts\Repository\EggRepositoryInterface
     */
    protected $repository;

    /**
     * EggScriptController constructor.
     *
     * @param \Prologue\Alerts\AlertsMessageBag                        $alert
     * @param \Amghost\Contracts\Repository\EggRepositoryInterface $repository
     * @param \Amghost\Services\Eggs\Scripts\InstallScriptService  $installScriptService
     */
    public function __construct(
        AlertsMessageBag $alert,
        EggRepositoryInterface $repository,
        InstallScriptService $installScriptService
    ) {
        $this->alert = $alert;
        $this->installScriptService = $installScriptService;
        $this->repository = $repository;
    }

    /**
     * Handle requests to render installation script for an Egg.
     *
     * @param int $egg
     * @return \Illuminate\View\View
     */
    public function index(int $egg): View
    {
        $egg = $this->repository->getWithCopyAttributes($egg);
        $copy = $this->repository->findWhere([
            ['copy_script_from', '=', null],
            ['nest_id', '=', $egg->nest_id],
            ['id', '!=', $egg],
        ]);

        $rely = $this->repository->findWhere([
            ['copy_script_from', '=', $egg->id],
        ]);

        return view('admin.eggs.scripts', [
            'copyFromOptions' => $copy,
            'relyOnScript' => $rely,
            'egg' => $egg,
        ]);
    }

    /**
     * Handle a request to update the installation script for an Egg.
     *
     * @param \Amghost\Http\Requests\Admin\Egg\EggScriptFormRequest $request
     * @param int                                                       $egg
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Amghost\Exceptions\Model\DataValidationException
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     * @throws \Amghost\Exceptions\Service\Egg\InvalidCopyFromException
     */
    public function update(EggScriptFormRequest $request, int $egg): RedirectResponse
    {
        $this->installScriptService->handle($egg, $request->normalize());
        $this->alert->success(trans('admin/nests.eggs.notices.script_updated'))->flash();

        return redirect()->route('admin.nests.egg.scripts', $egg);
    }
}
