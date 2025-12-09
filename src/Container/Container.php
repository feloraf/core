<?php

namespace Felora\Container;

use Closure;
use Exception;
use Felora\Contracts\Container\Container as ContainerContract;
use ReflectionFunction;

/**
 * Minimal IoC Container (bind, singleton, make)
 */
class Container implements ContainerContract
{
    /** @var array<string, \Closure|string> Bindings storage */
    private static array $bindings = [];

    /** @var string[] Shared (singleton) identifiers */
    private static array $shared = [];

    /** @var string[] Shared (instances) identifiers */
    private static array $instances = [];

    /** @var array<string, mixed> Resolved singleton instances */
    private static array $resolved = [];

    // -----------------------------
    // Protected helper getters/setters
    // -----------------------------

    // ---------- Bindings Helper function -----------
    protected function bindings(): array
    {
        return static::$bindings;
    }

    protected function setBind(string $abstract, $concrete): void
    {
        static::$bindings[$abstract] = $concrete;
    }

    protected function isBound(string $abstract): bool
    {
        return array_key_exists($abstract, static::$bindings);
    }

    protected function unsetBound(string $abstract): void
    {
        unset(static::$bindings[$abstract]);
    }

    // ---------- Shared Helper function -----------
    protected function setShare(string $abstract): void
    {
        static::$shared[$abstract] = $abstract;
    }

    protected function isShared(string $abstract): bool
    {
        return array_key_exists($abstract, static::$shared);
    }

    protected function unsetShared(string $abstract): void
    {
        unset(static::$shared[$abstract]);
    }

    // ---------- Instance Helper function -----------
    protected function setInstance($abstract): void
    {
        static::$instances[$abstract] = $abstract;
    }

    protected function unsetInstance($abstract): void
    {
        unset(static::$instances[$abstract]);
    }

    protected function isInstance($abstract): bool
    {
        return array_key_exists($abstract, static::$instances);
    }

    // ----------- Resolve Helper function -----------
    protected function getResolved(string $abstract): object|null
    {
        if(! array_key_exists($abstract, static::$resolved)) {
            return null;
        }

        return static::$resolved[$abstract];
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
        return $this->resolve($abstract, $parameters);
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
            $this->unsetSharedInstance($abstract);
            $this->setShare($abstract);
            $this->unsetResolved($abstract);
        }

        // If concrete is null, use abstract as concrete
        $this->setBind($abstract, $concrete ?? $abstract);
    }

    /**
     * @param string $abstract
     * @param object $instance
     * @return object
     */
    public function instance($abstract, $instance): object
    {
        // Remove abstract from global resolved instance
        $this->unsetSharedInstance($abstract);
        //set Instance
        $this->setInstance($abstract);
        $this->setResolved($abstract, $instance);

        return $this->getResolved($abstract);
    }

    public function bound($abstract) {
        return ($this->isBound($abstract) || $this->isInstance($abstract));
    }

    // -----------------------------
    // Protected resolver
    // -----------------------------

    /**
     * Resolve a concrete implementation
     *
     * @param string $abstract
     * @param array $parameters Optional constructor parameters
     * @return mixed
     * @throws Exception
     */
    protected function resolve($abstract, $parameters = [])
    {
        if (! $this->bound($abstract)) {
            throw new Exception("Abstract '{$abstract}' is not bound in the container.");
        }

        if($abstractIsBounded = $this->isBound($abstract)) {
            $concrete = $this->bindings()[$abstract];
        }

        $isBoundButNotShared = $abstractIsBounded && ! $this->isShared($abstract);

        // Return singleton|instance
        if (! $isBoundButNotShared && ! is_null($instance = $this->getResolved($abstract))) {
            return $instance;
        }

        $instance = null;

        if (is_callable($concrete)) {
            // Execute closure with container passed
            $instance = $this->resolveCallback($concrete, $parameters);
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
        if ($this->isShared($abstract) && (is_null($parameters) || empty($parameters))) {
            $this->setResolved($abstract, $instance);
        }

        return $instance;
    }

    /**
     * Resolve and execute a bound closure, injecting the container and parameters
     * when necessary.
     *
     * @param Closure $concrete   The closure representing the binding.
     * @param array   $parameters Parameters passed from make() to be injected into the closure.
     * @return mixed The resolved value returned by the closure.
     * @throws Exception If the first parameter of the closure is type-hinted incorrectly.
     */
    protected function resolveCallback(Closure $concrete, array $parameters)
    {
        $reflection = new ReflectionFunction($concrete);
        $paramCount = $reflection->getNumberOfParameters();

        if ($paramCount === 0) {
            return $concrete();
        }

        $firstParam = $reflection->getParameters()[0];
        $firstParamType = $firstParam->getType();

        if (
            $firstParamType !== null &&
            $firstParamType->getName() !== ContainerContract::class
        ) {
            throw new Exception(
                sprintf(
                    "The first parameter of the bind closure must be type-hinted as %s.",
                    ContainerContract::class
                )
            );
        }

        if ($paramCount === 1) {
            return $concrete($this);
        }

        return $concrete($this, $parameters);
    }

    protected function unsetSharedInstance($abstract): void
    {
        if($this->isShared($abstract)) {
            $this->unsetBound($abstract);
        }

        $this->unsetShared($abstract);
        $this->unsetResolved($abstract);
        $this->unsetInstance($abstract);
    }

    // -----------------------------
    // Static shortcut
    // -----------------------------

    public static function __callStatic($name, $arguments)
    {
        return (new static)->{$name}(...$arguments);
    }
}
