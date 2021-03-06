<?php

namespace Amghost\Http\Controllers\Server\Settings;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Amghost\Http\Controllers\Controller;
use Amghost\Traits\Controllers\JavascriptInjection;

class SftpController extends Controller
{
    use JavascriptInjection;

    /**
     * Render the server SFTP settings page.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Request $request): View
    {
        $this->authorize('access-sftp', $request->attributes->get('server'));
        $this->setRequest($request)->injectJavascript();

        return view('server.settings.sftp');
    }
}
