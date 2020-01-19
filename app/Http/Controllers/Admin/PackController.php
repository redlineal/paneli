<?php
/**
 * Amghost - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Amghost\Models\Pack;
use Prologue\Alerts\AlertsMessageBag;
use Amghost\Http\Controllers\Controller;
use Amghost\Services\Packs\ExportPackService;
use Amghost\Services\Packs\PackUpdateService;
use Amghost\Services\Packs\PackCreationService;
use Amghost\Services\Packs\PackDeletionService;
use Amghost\Http\Requests\Admin\PackFormRequest;
use Amghost\Services\Packs\TemplateUploadService;
use Amghost\Contracts\Repository\NestRepositoryInterface;
use Amghost\Contracts\Repository\PackRepositoryInterface;
use Illuminate\Contracts\Config\Repository as ConfigRepository;

class PackController extends Controller
{
    /**
     * @var \Prologue\Alerts\AlertsMessageBag
     */
    protected $alert;

    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;

    /**
     * @var \Amghost\Services\Packs\PackCreationService
     */
    protected $creationService;

    /**
     * @var \Amghost\Services\Packs\PackDeletionService
     */
    protected $deletionService;

    /**
     * @var \Amghost\Services\Packs\ExportPackService
     */
    protected $exportService;

    /**
     * @var \Amghost\Contracts\Repository\PackRepositoryInterface
     */
    protected $repository;

    /**
     * @var \Amghost\Services\Packs\PackUpdateService
     */
    protected $updateService;

    /**
     * @var \Amghost\Contracts\Repository\NestRepositoryInterface
     */
    protected $serviceRepository;

    /**
     * @var \Amghost\Services\Packs\TemplateUploadService
     */
    protected $templateUploadService;

    /**
     * PackController constructor.
     *
     * @param \Prologue\Alerts\AlertsMessageBag                         $alert
     * @param \Illuminate\Contracts\Config\Repository                   $config
     * @param \Amghost\Services\Packs\ExportPackService             $exportService
     * @param \Amghost\Services\Packs\PackCreationService           $creationService
     * @param \Amghost\Services\Packs\PackDeletionService           $deletionService
     * @param \Amghost\Contracts\Repository\PackRepositoryInterface $repository
     * @param \Amghost\Services\Packs\PackUpdateService             $updateService
     * @param \Amghost\Contracts\Repository\NestRepositoryInterface $serviceRepository
     * @param \Amghost\Services\Packs\TemplateUploadService         $templateUploadService
     */
    public function __construct(
        AlertsMessageBag $alert,
        ConfigRepository $config,
        ExportPackService $exportService,
        PackCreationService $creationService,
        PackDeletionService $deletionService,
        PackRepositoryInterface $repository,
        PackUpdateService $updateService,
        NestRepositoryInterface $serviceRepository,
        TemplateUploadService $templateUploadService
    ) {
        $this->alert = $alert;
        $this->config = $config;
        $this->creationService = $creationService;
        $this->deletionService = $deletionService;
        $this->exportService = $exportService;
        $this->repository = $repository;
        $this->updateService = $updateService;
        $this->serviceRepository = $serviceRepository;
        $this->templateUploadService = $templateUploadService;
    }

    /**
     * Display listing of all packs on the system.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        return view('admin.packs.index', [
            'packs' => $this->repository->setSearchTerm($request->input('query'))->paginateWithEggAndServerCount(),
        ]);
    }

    /**
     * Display new pack creation form.
     *
     * @return \Illuminate\View\View
     *
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function create()
    {
        return view('admin.packs.new', [
            'nests' => $this->serviceRepository->getWithEggs(),
        ]);
    }

    /**
     * Display new pack creation modal for use with template upload.
     *
     * @return \Illuminate\View\View
     *
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function newTemplate()
    {
        return view('admin.packs.modal', [
            'nests' => $this->serviceRepository->getWithEggs(),
        ]);
    }

    /**
     * Handle create pack request and route user to location.
     *
     * @param \Amghost\Http\Requests\Admin\PackFormRequest $request
     * @return \Illuminate\View\View
     *
     * @throws \Amghost\Exceptions\Model\DataValidationException
     * @throws \Amghost\Exceptions\Service\Pack\InvalidFileMimeTypeException
     * @throws \Amghost\Exceptions\Service\InvalidFileUploadException
     * @throws \Amghost\Exceptions\Service\Pack\InvalidPackArchiveFormatException
     * @throws \Amghost\Exceptions\Service\Pack\UnreadableZipArchiveException
     * @throws \Amghost\Exceptions\Service\Pack\ZipExtractionException
     */
    public function store(PackFormRequest $request)
    {
        if ($request->filled('from_template')) {
            $pack = $this->templateUploadService->handle($request->input('egg_id'), $request->file('file_upload'));
        } else {
            $pack = $this->creationService->handle($request->normalize(), $request->file('file_upload'));
        }

        $this->alert->success(trans('admin/pack.notices.pack_created'))->flash();

        return redirect()->route('admin.packs.view', $pack->id);
    }

    /**
     * Display pack view template to user.
     *
     * @param \Amghost\Models\Pack $pack
     * @return \Illuminate\View\View
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function view(Pack $pack)
    {
        return view('admin.packs.view', [
            'pack' => $this->repository->loadServerData($pack),
            'nests' => $this->serviceRepository->getWithEggs(),
        ]);
    }

    /**
     * Handle updating or deleting pack information.
     *
     * @param \Amghost\Http\Requests\Admin\PackFormRequest $request
     * @param \Amghost\Models\Pack                         $pack
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Amghost\Exceptions\Model\DataValidationException
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     * @throws \Amghost\Exceptions\Service\HasActiveServersException
     */
    public function update(PackFormRequest $request, Pack $pack)
    {
        $this->updateService->handle($pack, $request->normalize());
        $this->alert->success(trans('admin/pack.notices.pack_updated'))->flash();

        return redirect()->route('admin.packs.view', $pack->id);
    }

    /**
     * Delete a pack if no servers are attached to it currently.
     *
     * @param \Amghost\Models\Pack $pack
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     * @throws \Amghost\Exceptions\Service\HasActiveServersException
     */
    public function destroy(Pack $pack)
    {
        $this->deletionService->handle($pack->id);
        $this->alert->success(trans('admin/pack.notices.pack_deleted', [
            'name' => $pack->name,
        ]))->flash();

        return redirect()->route('admin.packs');
    }

    /**
     * Creates an archive of the pack and downloads it to the browser.
     *
     * @param \Amghost\Models\Pack $pack
     * @param bool|string              $files
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     * @throws \Amghost\Exceptions\Service\Pack\ZipArchiveCreationException
     */
    public function export(Pack $pack, $files = false)
    {
        $filename = $this->exportService->handle($pack, is_string($files));

        if (is_string($files)) {
            return response()->download($filename, 'pack-' . $pack->name . '.zip')->deleteFileAfterSend(true);
        }

        return response()->download($filename, 'pack-' . $pack->name . '.json', [
            'Content-Type' => 'application/json',
        ])->deleteFileAfterSend(true);
    }
}
