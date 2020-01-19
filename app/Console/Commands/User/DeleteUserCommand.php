<?php
/**
 * Amghost - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Console\Commands\User;

use Webmozart\Assert\Assert;
use Illuminate\Console\Command;
use Amghost\Services\Users\UserDeletionService;
use Amghost\Contracts\Repository\UserRepositoryInterface;

class DeleteUserCommand extends Command
{
    /**
     * @var \Amghost\Services\Users\UserDeletionService
     */
    protected $deletionService;

    /**
     * @var string
     */
    protected $description = 'Deletes a user from the Panel if no servers are attached to their account.';

    /**
     * @var \Amghost\Contracts\Repository\UserRepositoryInterface
     */
    protected $repository;

    /**
     * @var string
     */
    protected $signature = 'p:user:delete {--user=}';

    /**
     * DeleteUserCommand constructor.
     *
     * @param \Amghost\Services\Users\UserDeletionService           $deletionService
     * @param \Amghost\Contracts\Repository\UserRepositoryInterface $repository
     */
    public function __construct(
        UserDeletionService $deletionService,
        UserRepositoryInterface $repository
    ) {
        parent::__construct();

        $this->deletionService = $deletionService;
        $this->repository = $repository;
    }

    /**
     * @return bool
     * @throws \Amghost\Exceptions\DisplayException
     */
    public function handle()
    {
        $search = $this->option('user') ?? $this->ask(trans('command/messages.user.search_users'));
        Assert::notEmpty($search, 'Search term must be a non-null value, received %s.');

        $results = $this->repository->setSearchTerm($search)->all();
        if (count($results) < 1) {
            $this->error(trans('command/messages.user.no_users_found'));
            if ($this->input->isInteractive()) {
                return $this->handle();
            }

            return false;
        }

        if ($this->input->isInteractive()) {
            $tableValues = [];
            foreach ($results as $user) {
                $tableValues[] = [$user->id, $user->email, $user->name];
            }

            $this->table(['User ID', 'Email', 'Name'], $tableValues);
            if (! $deleteUser = $this->ask(trans('command/messages.user.select_search_user'))) {
                return $this->handle();
            }
        } else {
            if (count($results) > 1) {
                $this->error(trans('command/messages.user.multiple_found'));

                return false;
            }

            $deleteUser = $results->first();
        }

        if ($this->confirm(trans('command/messages.user.confirm_delete')) || ! $this->input->isInteractive()) {
            $this->deletionService->handle($deleteUser);
            $this->info(trans('command/messages.user.deleted'));
        }
    }
}
