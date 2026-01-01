<?php

namespace Felora\Support;

/**
 * Class PathResolver
 *
 * A lightweight, in-memory path registry for the framework.
 *
 * PathResolver acts as a centralized lookup table for resolving
 * application paths at runtime (e.g. base, config, cache).
 *
 * Design goals:
 * - Simple key-value storage
 * - No filesystem validation or IO
 * - Fast runtime resolution
 * - Flexible access via magic calls
 *
 * This class is intended to be registered as a singleton
 * in the service container.
 *
 * @package Felora\Support
 */
class PathResolver
{
    /**
     * Base path key identifier.
     *
     * This represents the root path of the application.
     */
    public const BASE_KEY = 'base';

    /**
     * Registered path items.
     *
     * Array keys represent logical path identifiers
     * (e.g. "base", "base.config"), and values are absolute paths.
     *
     * @var array<string, string>
     */
    private array $items = [];

    /**
     * Register or override a path value.
     *
     * @param string $key   Logical identifier for the path
     * @param string $value Absolute path value
     * @return void
     */
    public function set(string $key, string $value): void
    {
        $this->items[$key] = $value;
    }

    /**
     * Resolve a previously registered path.
     *
     * Returns null if the given key has not been registered.
     *
     * @param string $key Logical identifier for the path
     * @return string|null The resolved path or null if not found
     */
    public function get(string $key): ?string
    {
        return $this->items[$key] ?? null;
    }

    /**
     * Dynamically resolve paths using a fluent-style API.
     *
     * Examples:
     *  - $resolver->base()           => resolves "base"
     *  - $resolver->config()         => resolves "base.config"
     *  - $resolver->cache()          => resolves "base.cache"
     *
     * The magic call also proxies direct calls to get() and set().
     *
     * @param string $name
     * @param array<int, mixed> $arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        if ($name === 'get' || $name === 'set') {
            return $this->{$name}(...$arguments);
        }

        if ($name === self::BASE_KEY) {
            return $this->get(self::BASE_KEY);
        }

        return $this->get(self::BASE_KEY . '.' . $name);
    }
}
