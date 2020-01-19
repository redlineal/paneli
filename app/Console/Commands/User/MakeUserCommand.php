<?php
/**
 * Amghost - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Console\Commands\User;

use Illuminate\Console\Command;
use Amghost\Services\Users\UserCreationService;

class MakeUserCommand extends Command
{
    /**
     * @var \Amghost\Services\Users\UserCreationService
     */
    protected $creationService;

    /**
     * @var string
     */
    protected $description = 'Creates a user on the system via the CLI.';

    /**
     * @var string
     */
    protected $signature = 'p:user:make {--email=} {--username=} {--name-first=} {--name-last=} {--password=} {--admin=} {--no-password}';

    /**
     * MakeUserCommand constructor.
     *
     * @param \Amghost\Services\Users\UserCreationService $creationService
     */
    public function __construct(UserCreationService $creationService)
    {
        parent::__construct();

        $this->creationService = $creationService;
    }

    /**
     * Handle command request to create a new user.
     *
     * @throws \Exception
     * @throws \Amghost\Exceptions\Model\DataValidationException
     */
    public function handle()
    {
        $root_admin = $this->option('admin') ?? $this->confirm(trans('command/messages.user.ask_admin'));
        $email = $this->option('email') ?? $this->ask(trans('command/messages.user.ask_email'));
        $username = $this->option('username') ?? $this->ask(trans('command/messages.user.ask_username'));
        $name_first = $this->option('name-first') ?? $this->ask(trans('command/messages.user.ask_name_first'));
        $name_last = $this->option('name-last') ?? $this->ask(trans('command/messages.user.ask_name_last'));

        if (is_null($password = $this->option('password')) && ! $this->option('no-password')) {
            $this->warn(trans('command/messages.user.ask_password_help'));
            $this->line(trans('command/messages.user.ask_password_tip'));
            $password = $this->secret(trans('command/messages.user.ask_password'));
        }

        $user = $this->creationService->handle(compact('email', 'username', 'name_first', 'name_last', 'password', 'root_admin'));
        $this->table(['Field', 'Value'], [
            ['UUID', $user->uuid],
            ['Email', $user->email],
            ['Username', $user->username],
            ['Name', $user->name],
            ['Admin', $user->root_admin ? 'Yes' : 'No'],
        ]);
    }
}
