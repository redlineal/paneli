<?php

namespace Amghost\Http\Controllers\Admin\Settings;

use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Illuminate\Contracts\Console\Kernel;
use Amghost\Http\Controllers\Controller;
use Amghost\Traits\Helpers\AvailableLanguages;
use Amghost\Services\Helpers\SoftwareVersionService;
use Amghost\Contracts\Repository\SettingsRepositoryInterface;
use Amghost\Http\Requests\Admin\Settings\BaseSettingsFormRequest;

class IndexController extends Controller
{
    use AvailableLanguages;

    /**
     * @var \Prologue\Alerts\AlertsMessageBag
     */
    private $alert;

    /**
     * @var \Illuminate\Contracts\Console\Kernel
     */
    private $kernel;

    /**
     * @var \Amghost\Contracts\Repository\SettingsRepositoryInterface
     */
    private $settings;

    /**
     * @var \Amghost\Services\Helpers\SoftwareVersionService
     */
    private $versionService;

    /**
     * IndexController constructor.
     *
     * @param \Prologue\Alerts\AlertsMessageBag                             $alert
     * @param \Illuminate\Contracts\Console\Kernel                          $kernel
     * @param \Amghost\Contracts\Repository\SettingsRepositoryInterface $settings
     * @param \Amghost\Services\Helpers\SoftwareVersionService          $versionService
     */
    public function __construct(
        AlertsMessageBag $alert,
        Kernel $kernel,
        SettingsRepositoryInterface $settings,
        SoftwareVersionService $versionService)
    {
        $this->alert = $alert;
        $this->kernel = $kernel;
        $this->settings = $settings;
        $this->versionService = $versionService;
    }

    /**
     * Render the UI for basic Panel settings.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        return view('admin.settings.index', [
            'version' => $this->versionService,
            'languages' => $this->getAvailableLanguages(true),
        ]);
    }

    /**
     * Handle settings update.
     *
     * @param \Amghost\Http\Requests\Admin\Settings\BaseSettingsFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Amghost\Exceptions\Model\DataValidationException
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function update(BaseSettingsFormRequest $request): RedirectResponse
    {
        foreach ($request->normalize() as $key => $value) {
            $this->settings->set('settings::' . $key, $value);
        }

        $this->kernel->call('queue:restart');
        $this->alert->success('Panel settings have been updated successfully and the queue worker was restarted to apply these changes.')->flash();

        return redirect()->route('admin.settings');
    }
}
