<?php

namespace Amghost\Repositories\Eloquent;

use Amghost\Models\Setting;
use Amghost\Contracts\Repository\SettingsRepositoryInterface;

class SettingsRepository extends EloquentRepository implements SettingsRepositoryInterface
{
    /**
     * @var array
     */
    private static $cache = [];

    /**
     * @var array
     */
    private static $databaseMiss = [];

    /**
     * Return the model backing this repository.
     *
     * @return string
     */
    public function model()
    {
        return Setting::class;
    }

    /**
     * Store a new persistent setting in the database.
     *
     * @param string      $key
     * @param string|null $value
     *
     * @throws \Amghost\Exceptions\Model\DataValidationException
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function set(string $key, string $value = null)
    {
        // Clear item from the cache.
        $this->clearCache($key);
        $this->withoutFreshModel()->updateOrCreate(['key' => $key], ['value' => $value ?? '']);

        self::$cache[$key] = $value;
    }

    /**
     * Retrieve a persistent setting from the database.
     *
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        // If item has already been requested return it from the cache. If
        // we already know it is missing, immediately return the default value.
        if (array_key_exists($key, self::$cache)) {
            return self::$cache[$key];
        } elseif (array_key_exists($key, self::$databaseMiss)) {
            return value($default);
        }

        $instance = $this->getBuilder()->where('key', $key)->first();
        if (is_null($instance)) {
            self::$databaseMiss[$key] = true;

            return value($default);
        }

        return self::$cache[$key] = $instance->value;
    }

    /**
     * Remove a key from the database cache.
     *
     * @param string $key
     */
    public function forget(string $key)
    {
        $this->clearCache($key);
        $this->deleteWhere(['key' => $key]);
    }

    /**
     * Remove a key from the cache.
     *
     * @param string $key
     */
    private function clearCache(string $key)
    {
        unset(self::$cache[$key], self::$databaseMiss[$key]);
    }
}
