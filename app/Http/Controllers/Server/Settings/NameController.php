<?php

namespace Amghost\Http\Controllers\Server\Settings;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Amghost\Http\Controllers\Controller;
use Amghost\Traits\Controllers\JavascriptInjection;
use Amghost\Contracts\Repository\ServerRepositoryInterface;
use Amghost\Http\Requests\Server\Settings\ChangeServerNameRequest;

class NameController extends Controller
{
    use JavascriptInjection;

    /**
     * @var \Amghost\Contracts\Repository\ServerRepositoryInterface
     */
    private $repository;

    /**
     * NameController constructor.
     *
     * @param \Amghost\Contracts\Repository\ServerRepositoryInterface $repository
     */
    public function __construct(ServerRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Request $request)
    {
        $this->authorize('view-name', $request->attributes->get('server'));
        $this->setRequest($request)->injectJavascript();

        return view('server.settings.name');
    }

    /**
     * Update the stored name for a specific server.
     *
     * @param \Amghost\Http\Requests\Server\Settings\ChangeServerNameRequest $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Amghost\Exceptions\Model\DataValidationException
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function update(ChangeServerNameRequest $request): RedirectResponse
    {
        $this->repository->update($request->getServer()->id, $request->validated());

        return redirect()->route('server.settings.name', $request->getServer()->uuidShort);
    }
}
