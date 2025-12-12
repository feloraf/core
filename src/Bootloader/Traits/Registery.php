<?php
namespace Felora\Bootloader\Traits;

use Felora\Helpers\Path;
use Felora\Support\ConfigManager;
use Felora\Contracts\Helpers\Path as PathContract;
use Felora\Contracts\Support\ConfigManager as ConfigManagerContract;

trait Registery
{
    public function register(): void
    {
        $this->container->instance(PathContract::class, new Path([]));
        $this->container->instance(ConfigManagerContract::class, new ConfigManager($this->configPath(), $this->container));
    }
}