<?php
/**
 * AMGHOST - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Http\Middleware\Server;

use Closure;
use Illuminate\Http\Request;
use Amghost\Services\DaemonKeys\DaemonKeyProviderService;
use Amghost\Exceptions\Repository\RecordNotFoundException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AuthenticateAsSubuser
{
    /**
     * @var \Amghost\Services\DaemonKeys\DaemonKeyProviderService
     */
    private $keyProviderService;

    /**
     * SubuserAccessAuthenticate constructor.
     *
     * @param \Amghost\Services\DaemonKeys\DaemonKeyProviderService $keyProviderService
     */
    public function __construct(DaemonKeyProviderService $keyProviderService)
    {
        $this->keyProviderService = $keyProviderService;
    }

    /**
     * Determine if a subuser has permissions to access a server, if so set their access token.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @return mixed
     *
     * @throws \Amghost\Exceptions\Model\DataValidationException
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     */
    public function handle(Request $request, Closure $next)
    {
        $server = $request->attributes->get('server');

        try {
            $token = $this->keyProviderService->handle($server, $request->user());
        } catch (RecordNotFoundException $exception) {
            throw new AccessDeniedHttpException('This account does not have permission to access this server.');
        }

        $request->attributes->set('server_token', $token);

        return $next($request);
    }
}
