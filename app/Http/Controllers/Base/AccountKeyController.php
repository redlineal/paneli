<?php

namespace Amghost\Http\Controllers\Base;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Amghost\Models\ApiKey;
use Prologue\Alerts\AlertsMessageBag;
use Amghost\Http\Controllers\Controller;
use Amghost\Services\Api\KeyCreationService;
use Amghost\Http\Requests\Base\StoreAccountKeyRequest;
use Amghost\Contracts\Repository\ApiKeyRepositoryInterface;

class AccountKeyController extends Controller
{
    /**
     * @var \Prologue\Alerts\AlertsMessageBag
     */
    protected $alert;

    /**
     * @var \Amghost\Services\Api\KeyCreationService
     */
    protected $keyService;

    /**
     * @var \Amghost\Contracts\Repository\ApiKeyRepositoryInterface
     */
    protected $repository;

    /**
     * APIController constructor.
     *
     * @param \Prologue\Alerts\AlertsMessageBag                           $alert
     * @param \Amghost\Contracts\Repository\ApiKeyRepositoryInterface $repository
     * @param \Amghost\Services\Api\KeyCreationService                $keyService
     */
    public function __construct(
        AlertsMessageBag $alert,
        ApiKeyRepositoryInterface $repository,
        KeyCreationService $keyService
    ) {
        $this->alert = $alert;
        $this->keyService = $keyService;
        $this->repository = $repository;
    }

    /**
     * Display a listing of all account API keys.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request): View
    {
        return view('base.api.index', [
            'keys' => $this->repository->getAccountKeys($request->user()),
        ]);
    }

    /**
     * Display account API key creation page.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function create(Request $request): View
    {
        return view('base.api.new');
    }

    /**
     * Handle saving new account API key.
     *
     * @param \Amghost\Http\Requests\Base\StoreAccountKeyRequest $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Amghost\Exceptions\Model\DataValidationException
     */
    public function store(StoreAccountKeyRequest $request)
    {
        $this->keyService->setKeyType(ApiKey::TYPE_ACCOUNT)->handle([
            'user_id' => $request->user()->id,
            'allowed_ips' => $request->input('allowed_ips'),
            'memo' => $request->input('memo'),
        ]);

        $this->alert->success(trans('base.api.index.keypair_created'))->flash();

        return redirect()->route('account.api');
    }

    /**
     * Delete an account API key from the Panel via an AJAX request.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $identifier
     * @return \Illuminate\Http\Response
     */
    public function revoke(Request $request, string $identifier): Response
    {
        $this->repository->deleteAccountKey($request->user(), $identifier);

        return response('', 204);
    }
}
