<?php

namespace Amghost\Http\Controllers\Server;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Amghost\Http\Controllers\Controller;
use Amghost\Traits\Controllers\JavascriptInjection;
use Illuminate\Contracts\Config\Repository as ConfigRepository;

class ConsoleController extends Controller
{
    use JavascriptInjection;

    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;

    /**
     * ConsoleController constructor.
     *
     * @param \Illuminate\Contracts\Config\Repository $config
     */
    public function __construct(ConfigRepository $config)
    {
        $this->config = $config;
    }

    /**
     * Render server index page with the console and power options.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request): View
    {
        $server = $request->attributes->get('server');

        $this->setRequest($request)->injectJavascript([
            'server' => [
                'cpu' => $server->cpu,
            ],
            'meta' => [
                'saveFile' => route('server.files.save', $server->uuidShort),
                'csrfToken' => csrf_token(),
            ],
            'config' => [
                'console_count' => $this->config->get('amghost.console.count'),
                'console_freq' => $this->config->get('amghost.console.frequency'),
            ],
        ]);

        return view('server.index');
    }

    /**
     * Render a stand-alone console in the browser.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function console(Request $request): View
    {
        $this->setRequest($request)->injectJavascript(['config' => [
            'console_count' => $this->config->get('amghost.console.count'),
            'console_freq' => $this->config->get('amghost.console.frequency'),
        ]]);

        return view('server.console');
    }
}
