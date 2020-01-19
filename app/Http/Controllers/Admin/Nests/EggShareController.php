<?php
/**
 * Amghost - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Http\Controllers\Admin\Nests;

use Amghost\Models\Egg;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Amghost\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;
use Amghost\Services\Eggs\Sharing\EggExporterService;
use Amghost\Services\Eggs\Sharing\EggImporterService;
use Amghost\Http\Requests\Admin\Egg\EggImportFormRequest;
use Amghost\Services\Eggs\Sharing\EggUpdateImporterService;

class EggShareController extends Controller
{
    /**
     * @var \Prologue\Alerts\AlertsMessageBag
     */
    protected $alert;

    /**
     * @var \Amghost\Services\Eggs\Sharing\EggExporterService
     */
    protected $exporterService;

    /**
     * @var \Amghost\Services\Eggs\Sharing\EggImporterService
     */
    protected $importerService;

    /**
     * @var \Amghost\Services\Eggs\Sharing\EggUpdateImporterService
     */
    protected $updateImporterService;

    /**
     * OptionShareController constructor.
     *
     * @param \Prologue\Alerts\AlertsMessageBag                           $alert
     * @param \Amghost\Services\Eggs\Sharing\EggExporterService       $exporterService
     * @param \Amghost\Services\Eggs\Sharing\EggImporterService       $importerService
     * @param \Amghost\Services\Eggs\Sharing\EggUpdateImporterService $updateImporterService
     */
    public function __construct(
        AlertsMessageBag $alert,
        EggExporterService $exporterService,
        EggImporterService $importerService,
        EggUpdateImporterService $updateImporterService
    ) {
        $this->alert = $alert;
        $this->exporterService = $exporterService;
        $this->importerService = $importerService;
        $this->updateImporterService = $updateImporterService;
    }

    /**
     * @param \Amghost\Models\Egg $egg
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function export(Egg $egg): Response
    {
        $filename = trim(preg_replace('/[^\w]/', '-', kebab_case($egg->name)), '-');

        return response($this->exporterService->handle($egg->id), 200, [
            'Content-Transfer-Encoding' => 'binary',
            'Content-Description' => 'File Transfer',
            'Content-Disposition' => 'attachment; filename=egg-' . $filename . '.json',
            'Content-Type' => 'application/json',
        ]);
    }

    /**
     * Import a new service option using an XML file.
     *
     * @param \Amghost\Http\Requests\Admin\Egg\EggImportFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Amghost\Exceptions\Model\DataValidationException
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     * @throws \Amghost\Exceptions\Service\Egg\BadJsonFormatException
     * @throws \Amghost\Exceptions\Service\InvalidFileUploadException
     */
    public function import(EggImportFormRequest $request): RedirectResponse
    {
        $egg = $this->importerService->handle($request->file('import_file'), $request->input('import_to_nest'));
        $this->alert->success(trans('admin/nests.eggs.notices.imported'))->flash();

        return redirect()->route('admin.nests.egg.view', ['egg' => $egg->id]);
    }

    /**
     * Update an existing Egg using a new imported file.
     *
     * @param \Amghost\Http\Requests\Admin\Egg\EggImportFormRequest $request
     * @param int                                                       $egg
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Amghost\Exceptions\Model\DataValidationException
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     * @throws \Amghost\Exceptions\Service\Egg\BadJsonFormatException
     * @throws \Amghost\Exceptions\Service\InvalidFileUploadException
     */
    public function update(EggImportFormRequest $request, int $egg): RedirectResponse
    {
        $this->updateImporterService->handle($egg, $request->file('import_file'));
        $this->alert->success(trans('admin/nests.eggs.notices.updated_via_import'))->flash();

        return redirect()->route('admin.nests.egg.view', ['egg' => $egg]);
    }
}
