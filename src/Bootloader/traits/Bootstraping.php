<?php

namespace Felora\Bootloader\traits;

use Closure;
use Felora\Bootstrap\ {
    LoadConfig,
    ExceptionHandler,
    InitialRegistration,
};
use Felora\Contracts\Bootstrap\Bootstrap;

/**
 * @property \Felora\Contracts\Container\Container $container
 */
trait Bootstraping
{
    /**
     * @var array<Bootstrap>
     */
    private array $bootstraps = [
        0 => InitialRegistration::class,
        1 => LoadConfig::class,
        2 => ExceptionHandler::class,
    ];

    protected null|Closure $bootstrapping = null;

    protected null|Closure $bootstrapped = null;

    public function bootstrapping(Closure $callback): void
    {
        $this->bootstrapping = $callback;
    }

    public function bootstrapped(Closure $callback): void
    {
        $this->bootstrapped = $callback;
    }

    public function withBootstrap(string $bootstrap): void
    {
        $this->fire($bootstrap);
    }

    protected function bootstrappingFire(string $bootstrapClass): void
    {
        if(is_callable($this->bootstrapping))
        {
            ($this->bootstrapping)($bootstrapClass);
        }
    }

    protected function bootstrappedFire(Bootstrap $bootstrap): void
    {
        if(is_callable($this->bootstrapped))
        {
            ($this->bootstrapped)($bootstrap);
        }
    }

    protected function fire(string $bootstrap): void
    {
        $this->bootstrappingFire($bootstrap);

        $instance = (new $bootstrap);
        $instance->bootstrap($this->container);

        $this->container->instance($bootstrap, $instance);

        $this->bootstrappedFire($instance);
    }

    protected function loadBootstraps(): void
    {
        foreach($this->bootstraps as $bootstrap) {
            $this->fire((string)$bootstrap);
        }
    }
}
