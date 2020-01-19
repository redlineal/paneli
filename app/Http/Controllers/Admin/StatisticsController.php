<?php

namespace Amghost\Http\Controllers\Admin;

use Amghost\Http\Controllers\Controller;
use Amghost\Contracts\Repository\EggRepositoryInterface;
use Amghost\Traits\Controllers\PlainJavascriptInjection;
use Amghost\Contracts\Repository\NodeRepositoryInterface;
use Amghost\Contracts\Repository\UserRepositoryInterface;
use Amghost\Contracts\Repository\ServerRepositoryInterface;
use Amghost\Contracts\Repository\DatabaseRepositoryInterface;
use Amghost\Contracts\Repository\AllocationRepositoryInterface;

class StatisticsController extends Controller
{
    use PlainJavascriptInjection;

    private $allocationRepository;

    private $databaseRepository;

    private $eggRepository;

    private $nodeRepository;

    private $serverRepository;

    private $userRepository;

    public function __construct(
        AllocationRepositoryInterface $allocationRepository,
        DatabaseRepositoryInterface $databaseRepository,
        EggRepositoryInterface $eggRepository,
        NodeRepositoryInterface $nodeRepository,
        ServerRepositoryInterface $serverRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->allocationRepository = $allocationRepository;
        $this->databaseRepository = $databaseRepository;
        $this->eggRepository = $eggRepository;
        $this->nodeRepository = $nodeRepository;
        $this->serverRepository = $serverRepository;
        $this->userRepository = $userRepository;
    }

    public function index()
    {
        $servers = $this->serverRepository->all();
        $nodes = $this->nodeRepository->all();
        $usersCount = $this->userRepository->count();
        $eggsCount = $this->eggRepository->count();
        $databasesCount = $this->databaseRepository->count();
        $totalAllocations = $this->allocationRepository->count();
        $suspendedServersCount = $this->serverRepository->getSuspendedServersCount();

        $totalServerRam = 0;
        $totalNodeRam = 0;
        $totalServerDisk = 0;
        $totalNodeDisk = 0;
        foreach ($nodes as $node) {
            $stats = $this->nodeRepository->getUsageStatsRaw($node);
            $totalServerRam += $stats['memory']['value'];
            $totalNodeRam += $stats['memory']['max'];
            $totalServerDisk += $stats['disk']['value'];
            $totalNodeDisk += $stats['disk']['max'];
        }

        $tokens = [];
        foreach ($nodes as $node) {
            $tokens[$node->id] = $node->daemonSecret;
        }

        $this->injectJavascript([
            'servers' => $servers,
            'suspendedServers' => $suspendedServersCount,
            'totalServerRam' => $totalServerRam,
            'totalNodeRam' => $totalNodeRam,
            'totalServerDisk' => $totalServerDisk,
            'totalNodeDisk' => $totalNodeDisk,
            'nodes' => $nodes,
            'tokens' => $tokens,
        ]);

        return view('admin.statistics', [
            'servers' => $servers,
            'nodes' => $nodes,
            'usersCount' => $usersCount,
            'eggsCount' => $eggsCount,
            'totalServerRam' => $totalServerRam,
            'databasesCount' => $databasesCount,
            'totalNodeRam' => $totalNodeRam,
            'totalNodeDisk' => $totalNodeDisk,
            'totalServerDisk' => $totalServerDisk,
            'totalAllocations' => $totalAllocations,
        ]);
    }
}
