<?php
/**
 * Amghost - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Console\Commands\User;

use Illuminate\Console\Command;
use Amghost\Contracts\Repository\UserRepositoryInterface;

class DisableTwoFactorCommand extends Command
{
    /**
     * @var string
     */
    protected $description = 'Disable two-factor authentication for a specific user in the Panel.';

    /**
     * @var \Amghost\Contracts\Repository\UserRepositoryInterface
     */
    protected $repository;

    /**
     * @var string
     */
    protected $signature = 'p:user:disable2fa {--email= : The email of the user to disable 2-Factor for.}';

    /**
     * DisableTwoFactorCommand constructor.
     *
     * @param \Amghost\Contracts\Repository\UserRepositoryInterface $repository
     */
    public function __construct(UserRepositoryInterface $repository)
    {
        parent::__construct();

        $this->repository = $repository;
    }

    /**
     * Handle command execution process.
     *
     * @throws \Amghost\Exceptions\Model\DataValidationException
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function handle()
    {
        if ($this->input->isInteractive()) {
            $this->output->warning(trans('command/messages.user.2fa_help_text'));
        }

        $email = $this->option('email') ?? $this->ask(trans('command/messages.user.ask_email'));
        $user = $this->repository->setColumns(['id', 'email'])->findFirstWhere([['email', '=', $email]]);

        $this->repository->withoutFreshModel()->update($user->id, [
            'use_totp' => false,
            'totp_secret' => null,
        ]);
        $this->info(trans('command/messages.user.2fa_disabled', ['email' => $user->email]));
    }
}
