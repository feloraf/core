<?php

namespace Felora\Container;

use Exception;
use Felora\Contracts\Container\Container as ContainerContract;

/**
 * Minimal IoC Container (bind, singleton, make)
 */
class Container implements ContainerContract
{
    /** @var array<string, \Closure|string> Bindings storage */
    private static array $bindings = [];

    /** @var string[] Shared (singleton) identifiers */
    private static array $shared = [];

    /** @var array<string, mixed> Resolved singleton instances */
    private static array $resolved = [];

    // -----------------------------
    // Protected helper getters/setters
    // -----------------------------

    protected function bindings(): array
    {
        return static::$bindings;
    }

    protected function setBind(string $abstract, $concrete): void
    {
        static::$bindings[$abstract] = $concrete;
    }

    protected function shared(): array
    {
        return static::$shared;
    }

    protected function setShare(string $abstract): void
    {
        if (!in_array($abstract, static::$shared, true)) {
            static::$shared[] = $abstract;
        }
    }

    protected function isShared(string $abstract): bool
    {
        return in_array($abstract, static::$shared, true);
    }

    protected function resolved(): array
    {
        return static::$resolved;
    }

    protected function setResolved(string $abstract, $instance): void
    {
        static::$resolved[$abstract] = $instance;
    }

    protected function unsetResolved(string $abstract): void
    {
        unset(static::$resolved[$abstract]);
    }

    // -----------------------------
    // Public API
    // -----------------------------

    /**
     * Resolve an abstract from the container
     *
     * @param string $abstract
     * @param array $parameters Optional constructor parameters
     * @return mixed
     * @throws Exception
     */
    public function make(string $abstract, array $parameters = [])
    {
        if (!array_key_exists($abstract, $this->bindings())) {
            throw new Exception("Abstract '{$abstract}' is not bound in the container.");
        }

        return $this->resolve($this->bindings()[$abstract], $abstract, $parameters);
    }

    /**
     * Register a singleton binding
     *
     * @param string $abstract
     * @param \Closure|string|null $concrete
     */
    public function singleton(string $abstract, $concrete = null): void
    {
        $this->bind($abstract, $concrete, true);
    }

    /**
     * Register a binding in the container
     *
     * @param string $abstract
     * @param \Closure|string|null $concrete
     * @param bool $shared
     * @throws Exception
     */
    public function bind(string $abstract, $concrete = null, bool $shared = false): void
    {
        if (!is_string($abstract)) {
            throw new Exception("The abstract must be a string.");
        }

        // Handle shared (singleton)
        if ($shared) {
            $this->setShare($abstract);
            $this->unsetResolved($abstract);
        }

        // If concrete is null, use abstract as concrete
        $this->setBind($abstract, $concrete ?? $abstract);
    }

    // -----------------------------
    // Protected resolver
    // -----------------------------

    /**
     * Resolve a concrete implementation
     *
     * @param \Closure|string $concrete
     * @param string $abstract
     * @param array $parameters Optional constructor parameters
     * @return mixed
     * @throws Exception
     */
    protected function resolve($concrete, string $abstract, array $parameters = [])
    {
        // Return previously resolved singleton
        if ($this->isShared($abstract) && array_key_exists($abstract, $this->resolved())) {
            return $this->resolved()[$abstract];
        }

        $instance = null;

        if (is_callable($concrete)) {
            // Execute closure with container passed
            $instance = $concrete($this);
        } elseif (is_string($concrete)) {
            if (!class_exists($concrete)) {
                // Concrete is a string value, return as-is
                $instance = $concrete;
            } else {
                // Instantiate class (no auto-wiring yet)
                $instance = empty($parameters)
                    ? new $concrete()
                    : new $concrete(...$parameters);
            }
        } else {
            throw new Exception("Concrete for abstract '{$abstract}' must be a string or closure.");
        }

        // Store resolved singleton
        if ($this->isShared($abstract)) {
            $this->setResolved($abstract, $instance);
        }

        return $instance;
    }

    // -----------------------------
    // Static shortcut
    // -----------------------------

    public static function __callStatic($name, $arguments)
    {
        return (new static)->{$name}(...$arguments);
    }
}
