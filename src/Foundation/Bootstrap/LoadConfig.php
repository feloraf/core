<?php

namespace Felora\Foundation\Bootstrap;

use Felora\Contracts\Bootloader\BootloaderException;
use Felora\Contracts\Container\Container;
use Felora\Contracts\Foundation\Bootstrap\Bootstrap;
use Felora\Contracts\Support\ConfigManagerException;
use Felora\Contracts\Support\ConfigManager as ConfigManagerContract;
use Felora\Support\ConfigManager;

class LoadConfig implements Bootstrap
{
    public function bootstrap(Container $container): void
    {
        $base = $container->make('base');

        try {
            $configManager = new ConfigManager($base.'/config/app.php', $container);
        } catch (ConfigManagerException $e) {
            throw new BootloaderException($e->getMessage());
        }

        $container->instance(ConfigManagerContract::class, $configManager);
    }
}
