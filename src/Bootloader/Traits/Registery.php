<?php
namespace Felora\Bootloader\Traits;

use Felora\Contracts\Bootloader\BootloaderException;
use Felora\Helpers\Path\Path;
use Felora\Support\ConfigManager;
use Felora\Contracts\Helpers\Path\Path as PathContract;
use Felora\Contracts\Support\ConfigManager as ConfigManagerContract;
use Felora\Contracts\Support\ConfigManagerException;

trait Registery
{
    public function register(): void
    {
        $this->container->instance(PathContract::class, new Path([]));

        try {
            $configManager = new ConfigManager($this->configPath(), $this->container);
        } catch (ConfigManagerException $e) {
            throw new BootloaderException($e->getMessage());
        }

        $this->container->instance(ConfigManagerContract::class, $configManager);
    }
}