<?php

namespace Amghost\Http;

use Amghost\Models\ApiKey;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Auth\Middleware\Authenticate;
use Amghost\Http\Middleware\TrimStrings;
use Amghost\Http\Middleware\TrustProxies;
use Illuminate\Session\Middleware\StartSession;
use Amghost\Http\Middleware\EncryptCookies;
use Amghost\Http\Middleware\VerifyCsrfToken;
use Amghost\Http\Middleware\VerifyReCaptcha;
use Amghost\Http\Middleware\AdminAuthenticate;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Amghost\Http\Middleware\LanguageMiddleware;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Amghost\Http\Middleware\Api\AuthenticateKey;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Amghost\Http\Middleware\Api\SetSessionDriver;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Amghost\Http\Middleware\MaintenanceMiddleware;
use Amghost\Http\Middleware\RedirectIfAuthenticated;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Amghost\Http\Middleware\Api\AuthenticateIPAccess;
use Amghost\Http\Middleware\Api\ApiSubstituteBindings;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Amghost\Http\Middleware\Server\AccessingValidServer;
use Amghost\Http\Middleware\Server\AuthenticateAsSubuser;
use Amghost\Http\Middleware\Api\Daemon\DaemonAuthenticate;
use Amghost\Http\Middleware\Server\SubuserBelongsToServer;
use Amghost\Http\Middleware\RequireTwoFactorAuthentication;
use Amghost\Http\Middleware\Server\DatabaseBelongsToServer;
use Amghost\Http\Middleware\Server\ScheduleBelongsToServer;
use Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Amghost\Http\Middleware\Api\Client\SubstituteClientApiBindings;
use Amghost\Http\Middleware\Api\Application\AuthenticateApplicationUser;
use Amghost\Http\Middleware\DaemonAuthenticate as OldDaemonAuthenticate;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * @var array
     */
    protected $middleware = [
        CheckForMaintenanceMode::class,
        ValidatePostSize::class,
        TrimStrings::class,
        ConvertEmptyStringsToNull::class,
        TrustProxies::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            AuthenticateSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
            LanguageMiddleware::class,
            RequireTwoFactorAuthentication::class,
        ],
        'api' => [
            'throttle:120,1',
            ApiSubstituteBindings::class,
            SetSessionDriver::class,
            'api..key:' . ApiKey::TYPE_APPLICATION,
            AuthenticateApplicationUser::class,
            AuthenticateIPAccess::class,
        ],
        'client-api' => [
            'throttle:60,1',
            SubstituteClientApiBindings::class,
            SetSessionDriver::class,
            'api..key:' . ApiKey::TYPE_ACCOUNT,
            AuthenticateIPAccess::class,
        ],
        'daemon' => [
            SubstituteBindings::class,
            DaemonAuthenticate::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => Authenticate::class,
        'auth.basic' => AuthenticateWithBasicAuth::class,
        'guest' => RedirectIfAuthenticated::class,
        'server' => AccessingValidServer::class,
        'subuser.auth' => AuthenticateAsSubuser::class,
        'admin' => AdminAuthenticate::class,
        'daemon-old' => OldDaemonAuthenticate::class,
        'csrf' => VerifyCsrfToken::class,
        'throttle' => ThrottleRequests::class,
        'can' => Authorize::class,
        'bindings' => SubstituteBindings::class,
        'recaptcha' => VerifyReCaptcha::class,
        'node.maintenance' => MaintenanceMiddleware::class,

        // Server specific middleware (used for authenticating access to resources)
        //
        // These are only used for individual server authentication, and not global
        // actions from other resources. They are defined in the route files.
        'server..database' => DatabaseBelongsToServer::class,
        'server..subuser' => SubuserBelongsToServer::class,
        'server..schedule' => ScheduleBelongsToServer::class,

        // API Specific Middleware
        'api..key' => AuthenticateKey::class,
    ];
}
