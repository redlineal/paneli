<?php
/**
 * Amghost - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Http\Controllers\Admin\Nests;

use Illuminate\View\View;
use Amghost\Models\Egg;
use Amghost\Models\EggVariable;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Amghost\Http\Controllers\Controller;
use Amghost\Contracts\Repository\EggRepositoryInterface;
use Amghost\Services\Eggs\Variables\VariableUpdateService;
use Amghost\Http\Requests\Admin\Egg\EggVariableFormRequest;
use Amghost\Services\Eggs\Variables\VariableCreationService;
use Amghost\Contracts\Repository\EggVariableRepositoryInterface;

class EggVariableController extends Controller
{
    /**
     * @var \Prologue\Alerts\AlertsMessageBag
     */
    protected $alert;

    /**
     * @var \Amghost\Services\Eggs\Variables\VariableCreationService
     */
    protected $creationService;

    /**
     * @var \Amghost\Contracts\Repository\EggRepositoryInterface
     */
    protected $repository;

    /**
     * @var \Amghost\Services\Eggs\Variables\VariableUpdateService
     */
    protected $updateService;

    /**
     * @var \Amghost\Contracts\Repository\EggVariableRepositoryInterface
     */
    protected $variableRepository;

    /**
     * EggVariableController constructor.
     *
     * @param \Prologue\Alerts\AlertsMessageBag                                $alert
     * @param \Amghost\Services\Eggs\Variables\VariableCreationService     $creationService
     * @param \Amghost\Services\Eggs\Variables\VariableUpdateService       $updateService
     * @param \Amghost\Contracts\Repository\EggRepositoryInterface         $repository
     * @param \Amghost\Contracts\Repository\EggVariableRepositoryInterface $variableRepository
     */
    public function __construct(
        AlertsMessageBag $alert,
        VariableCreationService $creationService,
        VariableUpdateService $updateService,
        EggRepositoryInterface $repository,
        EggVariableRepositoryInterface $variableRepository
    ) {
        $this->alert = $alert;
        $this->creationService = $creationService;
        $this->repository = $repository;
        $this->updateService = $updateService;
        $this->variableRepository = $variableRepository;
    }

    /**
     * Handle request to view the variables attached to an Egg.
     *
     * @param int $egg
     * @return \Illuminate\View\View
     *
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function view(int $egg): View
    {
        $egg = $this->repository->getWithVariables($egg);

        return view('admin.eggs.variables', ['egg' => $egg]);
    }

    /**
     * Handle a request to create a new Egg variable.
     *
     * @param \Amghost\Http\Requests\Admin\Egg\EggVariableFormRequest $request
     * @param \Amghost\Models\Egg $egg
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Amghost\Exceptions\Model\DataValidationException
     * @throws \Amghost\Exceptions\Service\Egg\Variable\BadValidationRuleException
     * @throws \Amghost\Exceptions\Service\Egg\Variable\ReservedVariableNameException
     */
    public function store(EggVariableFormRequest $request, Egg $egg): RedirectResponse
    {
        $this->creationService->handle($egg->id, $request->normalize());
        $this->alert->success(trans('admin/nests.variables.notices.variable_created'))->flash();

        return redirect()->route('admin.nests.egg.variables', $egg->id);
    }

    /**
     * Handle a request to update an existing Egg variable.
     *
     * @param \Amghost\Http\Requests\Admin\Egg\EggVariableFormRequest $request
     * @param \Amghost\Models\Egg                                     $egg
     * @param \Amghost\Models\EggVariable                             $variable
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Amghost\Exceptions\DisplayException
     * @throws \Amghost\Exceptions\Model\DataValidationException
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     * @throws \Amghost\Exceptions\Service\Egg\Variable\ReservedVariableNameException
     */
    public function update(EggVariableFormRequest $request, Egg $egg, EggVariable $variable): RedirectResponse
    {
        $this->updateService->handle($variable, $request->normalize());
        $this->alert->success(trans('admin/nests.variables.notices.variable_updated', [
            'variable' => $variable->name,
        ]))->flash();

        return redirect()->route('admin.nests.egg.variables', $egg->id);
    }

    /**
     * Handle a request to delete an existing Egg variable from the Panel.
     *
     * @param int                             $egg
     * @param \Amghost\Models\EggVariable $variable
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(int $egg, EggVariable $variable): RedirectResponse
    {
        $this->variableRepository->delete($variable->id);
        $this->alert->success(trans('admin/nests.variables.notices.variable_deleted', [
            'variable' => $variable->name,
        ]))->flash();

        return redirect()->route('admin.nests.egg.variables', $egg);
    }
}
