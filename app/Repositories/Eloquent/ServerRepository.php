<?php

namespace Amghost\Repositories\Eloquent;

use Amghost\Models\Node;
use Amghost\Models\User;
use Webmozart\Assert\Assert;
use Amghost\Models\Server;
use Illuminate\Support\Collection;
use Amghost\Repositories\Concerns\Searchable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Amghost\Exceptions\Repository\RecordNotFoundException;
use Amghost\Contracts\Repository\ServerRepositoryInterface;

class ServerRepository extends EloquentRepository implements ServerRepositoryInterface
{
    use Searchable;

    /**
     * Return the model backing this repository.
     *
     * @return string
     */
    public function model()
    {
        return Server::class;
    }

    /**
     * Returns a listing of all servers that exist including relationships.
     *
     * @param int $paginate
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllServers(int $paginate): LengthAwarePaginator
    {
        $instance = $this->getBuilder()->with('node', 'user', 'allocation')->search($this->getSearchTerm());

        return $instance->paginate($paginate, $this->getColumns());
    }

    /**
     * Load the egg relations onto the server model.
     *
     * @param \Amghost\Models\Server $server
     * @param bool                       $refresh
     * @return \Amghost\Models\Server
     */
    public function loadEggRelations(Server $server, bool $refresh = false): Server
    {
        if (! $server->relationLoaded('egg') || $refresh) {
            $server->load('egg.scriptFrom');
        }

        return $server;
    }

    /**
     * Return a collection of servers with their associated data for rebuild operations.
     *
     * @param int|null $server
     * @param int|null $node
     * @return \Illuminate\Support\Collection
     */
    public function getDataForRebuild(int $server = null, int $node = null): Collection
    {
        $instance = $this->getBuilder()->with(['allocation', 'allocations', 'pack', 'egg', 'node']);

        if (! is_null($server) && is_null($node)) {
            $instance = $instance->where('id', '=', $server);
        } elseif (is_null($server) && ! is_null($node)) {
            $instance = $instance->where('node_id', '=', $node);
        }

        return $instance->get($this->getColumns());
    }

    /**
     * Return a server model and all variables associated with the server.
     *
     * @param int $id
     * @return \Amghost\Models\Server
     *
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function findWithVariables(int $id): Server
    {
        try {
            return $this->getBuilder()->with('egg.variables', 'variables')
                ->where($this->getModel()->getKeyName(), '=', $id)
                ->firstOrFail($this->getColumns());
        } catch (ModelNotFoundException $exception) {
            throw new RecordNotFoundException;
        }
    }

    /**
     * Get the primary allocation for a given server. If a model is passed into
     * the function, load the allocation relationship onto it. Otherwise, find and
     * return the server from the database.
     *
     * @param \Amghost\Models\Server $server
     * @param bool                       $refresh
     * @return \Amghost\Models\Server
     */
    public function getPrimaryAllocation(Server $server, bool $refresh = false): Server
    {
        if (! $server->relationLoaded('allocation') || $refresh) {
            $server->load('allocation');
        }

        return $server;
    }

    /**
     * Return all of the server variables possible and default to the variable
     * default if there is no value defined for the specific server requested.
     *
     * @param int  $id
     * @param bool $returnAsObject
     * @return array|object
     *
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function getVariablesWithValues(int $id, bool $returnAsObject = false)
    {
        try {
            $instance = $this->getBuilder()->with('variables', 'egg.variables')->find($id, $this->getColumns());
        } catch (ModelNotFoundException $exception) {
            throw new RecordNotFoundException;
        }

        $data = [];
        $instance->getRelation('egg')->getRelation('variables')->each(function ($item) use (&$data, $instance) {
            $display = $instance->getRelation('variables')->where('variable_id', $item->id)->pluck('variable_value')->first();

            $data[$item->env_variable] = $display ?? $item->default_value;
        });

        if ($returnAsObject) {
            return (object) [
                'data' => $data,
                'server' => $instance,
            ];
        }

        return $data;
    }

    /**
     * Return enough data to be used for the creation of a server via the daemon.
     *
     * @param \Amghost\Models\Server $server
     * @param bool                       $refresh
     * @return \Amghost\Models\Server
     */
    public function getDataForCreation(Server $server, bool $refresh = false): Server
    {
        foreach (['allocation', 'allocations', 'pack', 'egg'] as $relation) {
            if (! $server->relationLoaded($relation) || $refresh) {
                $server->load($relation);
            }
        }

        return $server;
    }

    /**
     * Load associated databases onto the server model.
     *
     * @param \Amghost\Models\Server $server
     * @param bool                       $refresh
     * @return \Amghost\Models\Server
     */
    public function loadDatabaseRelations(Server $server, bool $refresh = false): Server
    {
        if (! $server->relationLoaded('databases') || $refresh) {
            $server->load('databases.host');
        }

        return $server;
    }

    /**
     * Get data for use when updating a server on the Daemon. Returns an array of
     * the egg and pack UUID which are used for build and rebuild. Only loads relations
     * if they are missing, or refresh is set to true.
     *
     * @param \Amghost\Models\Server $server
     * @param bool                       $refresh
     * @return array
     */
    public function getDaemonServiceData(Server $server, bool $refresh = false): array
    {
        if (! $server->relationLoaded('egg') || $refresh) {
            $server->load('egg');
        }

        if (! $server->relationLoaded('pack') || $refresh) {
            $server->load('pack');
        }

        return [
            'egg' => $server->getRelation('egg')->uuid,
            'pack' => is_null($server->getRelation('pack')) ? null : $server->getRelation('pack')->uuid,
        ];
    }

    /**
     * Return a paginated list of servers that a user can access at a given level.
     *
     * @param \Amghost\Models\User $user
     * @param int                      $level
     * @param bool|int                 $paginate
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Database\Eloquent\Collection
     */
    public function filterUserAccessServers(User $user, int $level, $paginate = 25)
    {
        $instance = $this->getBuilder()->select($this->getColumns())->with(['user', 'node', 'allocation']);

        // If access level is set to owner, only display servers
        // that the user owns.
        if ($level === User::FILTER_LEVEL_OWNER) {
            $instance->where('owner_id', $user->id);
        }

        // If set to all, display all servers they can access, including
        // those they access as an admin. If set to subuser, only return
        // the servers they can access because they are owner, or marked
        // as a subuser of the server.
        elseif (($level === User::FILTER_LEVEL_ALL && ! $user->root_admin) || $level === User::FILTER_LEVEL_SUBUSER) {
            $instance->whereIn('id', $this->getUserAccessServers($user->id));
        }

        // If set to admin, only display the servers a user can access
        // as an administrator (leaves out owned and subuser of).
        elseif ($level === User::FILTER_LEVEL_ADMIN && $user->root_admin) {
            $instance->whereNotIn('id', $this->getUserAccessServers($user->id));
        }

        $instance->search($this->getSearchTerm());

        return $paginate ? $instance->paginate($paginate) : $instance->get();
    }

    /**
     * Return a server by UUID.
     *
     * @param string $uuid
     * @return \Amghost\Models\Server
     *
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function getByUuid(string $uuid): Server
    {
        Assert::notEmpty($uuid, 'Expected non-empty string as first argument passed to ' . __METHOD__);

        try {
            return $this->getBuilder()->with('nest', 'node')->where(function ($query) use ($uuid) {
                $query->where('uuidShort', $uuid)->orWhere('uuid', $uuid);
            })->firstOrFail($this->getColumns());
        } catch (ModelNotFoundException $exception) {
            throw new RecordNotFoundException;
        }
    }

    /**
     * Return all of the servers that should have a power action performed against them.
     *
     * @param int[] $servers
     * @param int[] $nodes
     * @param bool  $returnCount
     * @return int|\Generator
     */
    public function getServersForPowerAction(array $servers = [], array $nodes = [], bool $returnCount = false)
    {
        $instance = $this->getBuilder();

        if (! empty($nodes) && ! empty($servers)) {
            $instance->whereIn('id', $servers)->orWhereIn('node_id', $nodes);
        } elseif (empty($nodes) && ! empty($servers)) {
            $instance->whereIn('id', $servers);
        } elseif (! empty($nodes) && empty($servers)) {
            $instance->whereIn('node_id', $nodes);
        }

        if ($returnCount) {
            return $instance->count();
        }

        return $instance->with('node')->cursor();
    }

    /**
     * Return the total number of servers that will be affected by the query.
     *
     * @param int[] $servers
     * @param int[] $nodes
     * @return int
     */
    public function getServersForPowerActionCount(array $servers = [], array $nodes = []): int
    {
        return $this->getServersForPowerAction($servers, $nodes, true);
    }

    /**
     * Check if a given UUID and UUID-Short string are unique to a server.
     *
     * @param string $uuid
     * @param string $short
     * @return bool
     */
    public function isUniqueUuidCombo(string $uuid, string $short): bool
    {
        return ! $this->getBuilder()->where('uuid', '=', $uuid)->orWhere('uuidShort', '=', $short)->exists();
    }

    /**
     * Return an array of server IDs that a given user can access based
     * on owner and subuser permissions.
     *
     * @param int $user
     * @return int[]
     */
    private function getUserAccessServers(int $user): array
    {
        return $this->getBuilder()->select('id')->where('owner_id', $user)->union(
            $this->app->make(SubuserRepository::class)->getBuilder()->select('server_id')->where('user_id', $user)
        )->pluck('id')->all();
    }

    /**
     * Get the amount of servers that are suspended.
     *
     * @return int
     */
    public function getSuspendedServersCount(): int
    {
        return $this->getBuilder()->where('suspended', true)->count();
    }

    /**
     * Returns all of the servers that exist for a given node in a paginated response.
     *
     * @param int $node
     * @param int $limit
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function loadAllServersForNode(int $node, int $limit): LengthAwarePaginator
    {
        return $this->getBuilder()
            ->with(['user', 'nest', 'egg'])
            ->where('node_id', '=', $node)
            ->paginate($limit);
    }
}
