<?php

namespace Felora\Bootstrap;

use Closure;
use Felora\Container\Container;
use Felora\Contracts\Container\Container as ContainerContract;

/** Bootstraps */
use Felora\Foundation\Bootstrap\ {
    LoadConfig,
    ExceptionHandler,
    InitialRegistration,
};
use Felora\Contracts\Foundation\Bootstrap\Bootstrap as BootstrapContract;

class Bootstrap extends Container
{
    /**
     * @var array<BootstrapContract>
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

    protected function bootstrappedFire(BootstrapContract $bootstrap): void
    {
        if(is_callable($this->bootstrapped))
        {
            ($this->bootstrapped)($bootstrap);
        }
    }

    protected function fire(string $bootstrap): void
    {
        $this->bootstrappingFire($bootstrap);

        /** @var BootstrapContract */
        $instance = (new $bootstrap);
        /** @var  ContainerContract $container*/
        $instance->bootstrap($container = $this);

        $this->instance($bootstrap, $instance);

        $this->bootstrappedFire($instance);
    }

    protected function loadBootstraps(): void
    {
        foreach($this->bootstraps as $bootstrap) {
            $this->fire((string)$bootstrap);
        }
    }
}
