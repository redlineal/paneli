<?php

namespace Amghost\Providers;

use Illuminate\Support\ServiceProvider;
use Amghost\Repositories\Daemon\FileRepository;
use Amghost\Repositories\Daemon\PowerRepository;
use Amghost\Repositories\Eloquent\EggRepository;
use Amghost\Repositories\Eloquent\NestRepository;
use Amghost\Repositories\Eloquent\NodeRepository;
use Amghost\Repositories\Eloquent\PackRepository;
use Amghost\Repositories\Eloquent\TaskRepository;
use Amghost\Repositories\Eloquent\UserRepository;
use Amghost\Repositories\Daemon\CommandRepository;
use Amghost\Repositories\Eloquent\ApiKeyRepository;
use Amghost\Repositories\Eloquent\ServerRepository;
use Amghost\Repositories\Eloquent\SessionRepository;
use Amghost\Repositories\Eloquent\SubuserRepository;
use Amghost\Repositories\Eloquent\DatabaseRepository;
use Amghost\Repositories\Eloquent\LocationRepository;
use Amghost\Repositories\Eloquent\ScheduleRepository;
use Amghost\Repositories\Eloquent\SettingsRepository;
use Amghost\Repositories\Eloquent\DaemonKeyRepository;
use Amghost\Repositories\Eloquent\AllocationRepository;
use Amghost\Repositories\Eloquent\PermissionRepository;
use Amghost\Contracts\Repository\EggRepositoryInterface;
use Amghost\Repositories\Daemon\ConfigurationRepository;
use Amghost\Repositories\Eloquent\EggVariableRepository;
use Amghost\Contracts\Repository\NestRepositoryInterface;
use Amghost\Contracts\Repository\NodeRepositoryInterface;
use Amghost\Contracts\Repository\PackRepositoryInterface;
use Amghost\Contracts\Repository\TaskRepositoryInterface;
use Amghost\Contracts\Repository\UserRepositoryInterface;
use Amghost\Repositories\Eloquent\DatabaseHostRepository;
use Amghost\Contracts\Repository\ApiKeyRepositoryInterface;
use Amghost\Contracts\Repository\ServerRepositoryInterface;
use Amghost\Repositories\Eloquent\ServerVariableRepository;
use Amghost\Contracts\Repository\SessionRepositoryInterface;
use Amghost\Contracts\Repository\SubuserRepositoryInterface;
use Amghost\Contracts\Repository\DatabaseRepositoryInterface;
use Amghost\Contracts\Repository\LocationRepositoryInterface;
use Amghost\Contracts\Repository\ScheduleRepositoryInterface;
use Amghost\Contracts\Repository\SettingsRepositoryInterface;
use Amghost\Contracts\Repository\DaemonKeyRepositoryInterface;
use Amghost\Contracts\Repository\AllocationRepositoryInterface;
use Amghost\Contracts\Repository\PermissionRepositoryInterface;
use Amghost\Contracts\Repository\Daemon\FileRepositoryInterface;
use Amghost\Contracts\Repository\EggVariableRepositoryInterface;
use Amghost\Contracts\Repository\Daemon\PowerRepositoryInterface;
use Amghost\Contracts\Repository\DatabaseHostRepositoryInterface;
use Amghost\Contracts\Repository\Daemon\CommandRepositoryInterface;
use Amghost\Contracts\Repository\ServerVariableRepositoryInterface;
use Amghost\Contracts\Repository\Daemon\ConfigurationRepositoryInterface;
use Amghost\Repositories\Daemon\ServerRepository as DaemonServerRepository;
use Amghost\Contracts\Repository\Daemon\ServerRepositoryInterface as DaemonServerRepositoryInterface;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register all of the repository bindings.
     */
    public function register()
    {
        // Eloquent Repositories
        $this->app->bind(AllocationRepositoryInterface::class, AllocationRepository::class);
        $this->app->bind(ApiKeyRepositoryInterface::class, ApiKeyRepository::class);
        $this->app->bind(DaemonKeyRepositoryInterface::class, DaemonKeyRepository::class);
        $this->app->bind(DatabaseRepositoryInterface::class, DatabaseRepository::class);
        $this->app->bind(DatabaseHostRepositoryInterface::class, DatabaseHostRepository::class);
        $this->app->bind(EggRepositoryInterface::class, EggRepository::class);
        $this->app->bind(EggVariableRepositoryInterface::class, EggVariableRepository::class);
        $this->app->bind(LocationRepositoryInterface::class, LocationRepository::class);
        $this->app->bind(NestRepositoryInterface::class, NestRepository::class);
        $this->app->bind(NodeRepositoryInterface::class, NodeRepository::class);
        $this->app->bind(PackRepositoryInterface::class, PackRepository::class);
        $this->app->bind(PermissionRepositoryInterface::class, PermissionRepository::class);
        $this->app->bind(ScheduleRepositoryInterface::class, ScheduleRepository::class);
        $this->app->bind(ServerRepositoryInterface::class, ServerRepository::class);
        $this->app->bind(ServerVariableRepositoryInterface::class, ServerVariableRepository::class);
        $this->app->bind(SessionRepositoryInterface::class, SessionRepository::class);
        $this->app->bind(SettingsRepositoryInterface::class, SettingsRepository::class);
        $this->app->bind(SubuserRepositoryInterface::class, SubuserRepository::class);
        $this->app->bind(TaskRepositoryInterface::class, TaskRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);

        // Daemon Repositories
        $this->app->bind(ConfigurationRepositoryInterface::class, ConfigurationRepository::class);
        $this->app->bind(CommandRepositoryInterface::class, CommandRepository::class);
        $this->app->bind(DaemonServerRepositoryInterface::class, DaemonServerRepository::class);
        $this->app->bind(FileRepositoryInterface::class, FileRepository::class);
        $this->app->bind(PowerRepositoryInterface::class, PowerRepository::class);
    }
}
