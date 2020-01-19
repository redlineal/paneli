<?php

namespace Amghost\Http\Controllers\Server\Settings;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Amghost\Models\User;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Amghost\Http\Controllers\Controller;
use Amghost\Traits\Controllers\JavascriptInjection;
use Amghost\Services\Servers\StartupCommandViewService;
use Amghost\Services\Servers\StartupModificationService;
use Amghost\Http\Requests\Server\UpdateStartupParametersFormRequest;

class StartupController extends Controller
{
    use JavascriptInjection;

    /**
     * @var \Prologue\Alerts\AlertsMessageBag
     */
    private $alert;

    /**
     * @var \Amghost\Services\Servers\StartupCommandViewService
     */
    private $commandViewService;

    /**
     * @var \Amghost\Services\Servers\StartupModificationService
     */
    private $modificationService;

    /**
     * StartupController constructor.
     *
     * @param \Prologue\Alerts\AlertsMessageBag                        $alert
     * @param \Amghost\Services\Servers\StartupCommandViewService  $commandViewService
     * @param \Amghost\Services\Servers\StartupModificationService $modificationService
     */
    public function __construct(
        AlertsMessageBag $alert,
        StartupCommandViewService $commandViewService,
        StartupModificationService $modificationService
    ) {
        $this->alert = $alert;
        $this->commandViewService = $commandViewService;
        $this->modificationService = $modificationService;
    }

    /**
     * Render the server startup page.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function index(Request $request): View
    {
        $server = $request->attributes->get('server');
        $this->authorize('view-startup', $server);
        $this->setRequest($request)->injectJavascript();

        $data = $this->commandViewService->handle($server->id);

        return view('server.settings.startup', [
            'variables' => $data->get('variables'),
            'server_values' => $data->get('server_values'),
            'startup' => $data->get('startup'),
        ]);
    }

    /**
     * Handle request to update the startup variables for a server. Authorization
     * is handled in the form request.
     *
     * @param \Amghost\Http\Requests\Server\UpdateStartupParametersFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Amghost\Exceptions\DisplayException
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Amghost\Exceptions\Model\DataValidationException
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function update(UpdateStartupParametersFormRequest $request): RedirectResponse
    {
        $this->modificationService->setUserLevel(User::USER_LEVEL_USER);
        $this->modificationService->handle($request->attributes->get('server'), $request->normalize());
        $this->alert->success(trans('server.config.startup.edited'))->flash();

        return redirect()->route('server.settings.startup', ['server' => $request->attributes->get('server')->uuidShort]);
    }
}
