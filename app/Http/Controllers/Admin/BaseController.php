<?php

namespace Amghost\Http\Controllers\Admin;

use Illuminate\View\View;
use Amghost\Http\Controllers\Controller;
use Amghost\Services\Helpers\SoftwareVersionService;

class BaseController extends Controller
{
    /**
     * @var \Amghost\Services\Helpers\SoftwareVersionService
     */
    private $version;

    /**
     * BaseController constructor.
     *
     * @param \Amghost\Services\Helpers\SoftwareVersionService $version
     */
    public function __construct(SoftwareVersionService $version)
    {
        $this->version = $version;
    }

    /**
     * Return the admin index view.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        return view('admin.index', ['version' => $this->version]);
    }
}
