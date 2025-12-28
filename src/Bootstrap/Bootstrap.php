<?php

namespace Felora\Bootstrap;

use Closure;
use Felora\Container\Container;
use Felora\Contracts\Container\Container as ContainerContract;
use Felora\Contracts\Foundation\Bootstrap\Bootstrap as BootstrapContract;

/** Bootstraps */
use Felora\Foundation\Bootstrap\{
    LoadConfig,
    ExceptionHandler,
    InitialRegistration,
};

/**
 * Class Bootstrap
 *
 * Responsible for loading and executing all defined bootstraps.
 * Allows registering callbacks before and after each bootstrap execution.
 *
 * @package Felora\Bootstrap
 */
class Bootstrap extends Container
{
    /**
     * List of bootstrap classes to load.
     *
     * @var array<class-string<BootstrapContract>>
     */
    private array $bootstraps = [
        0 => InitialRegistration::class,
        1 => LoadConfig::class,
        2 => ExceptionHandler::class,
    ];

    /**
     * Callback executed before each bootstrap class is booted.
     *
     * @var null|Closure(string $bootstrapClass)
     */
    protected ?Closure $bootstrapping = null;

    /**
     * Callback executed after each bootstrap class has been booted.
     *
     * @var null|Closure(BootstrapContract $bootstrapInstance)
     */
    protected ?Closure $bootstrapped = null;

    /**
     * Register a callback to be executed before each bootstrap.
     *
     * @param Closure(string $bootstrapClass) $callback
     */
    public function bootstrapping(Closure $callback): void
    {
        $this->bootstrapping = $callback;
    }

    /**
     * Register a callback to be executed after each bootstrap.
     *
     * @param Closure(BootstrapContract $bootstrapInstance) $callback
     */
    public function bootstrapped(Closure $callback): void
    {
        $this->bootstrapped = $callback;
    }

    /**
     * Execute a single bootstrap class immediately.
     *
     * @param class-string<BootstrapContract> $bootstrap
     */
    public function withBootstrap(string $bootstrap): void
    {
        $this->fire($bootstrap);
    }

    /**
     * Fire the "bootstrapping" callback before executing a bootstrap.
     *
     * @param string $bootstrapClass
     */
    protected function bootstrappingFire(string $bootstrapClass): void
    {
        if (is_callable($this->bootstrapping)) {
            ($this->bootstrapping)($bootstrapClass);
        }
    }

    /**
     * Fire the "bootstrapped" callback after executing a bootstrap.
     *
     * @param BootstrapContract $bootstrap
     */
    protected function bootstrappedFire(BootstrapContract $bootstrap): void
    {
        if (is_callable($this->bootstrapped)) {
            ($this->bootstrapped)($bootstrap);
        }
    }

    /**
     * Boot a single bootstrap class, triggering before/after callbacks.
     *
     * @param class-string<BootstrapContract> $bootstrap
     */
    protected function fire(string $bootstrap): void
    {
        $this->bootstrappingFire($bootstrap);

        /** @var BootstrapContract $instance */
        $instance = new $bootstrap();
        /** @var ContainerContract $container */
        $instance->bootstrap($container = $this);

        $this->instance($bootstrap, $instance);

        $this->bootstrappedFire($instance);
    }

    /**
     * Load and execute all bootstraps in the defined order.
     */
    protected function loadBootstraps(): void
    {
        foreach ($this->bootstraps as $bootstrap) {
            $this->fire((string) $bootstrap);
        }
    }
}
