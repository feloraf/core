<?php
namespace Felora\Bootloader;

use Felora\Bootloader\Traits\Registery;
use Felora\Container\Container;
use Felora\Contracts\Container\Container as ContainerContract;

class Bootloader
{
    use Registery;

    protected ContainerContract $container;

    public function __construct()
    {
        $this->container = new Container;

        $this->handle();
    }

    private function handle(): void
    {
        $this->register();

        if (!method_exists($this, 'server')) {
            throw new BootloaderException('Bootloader requires the "server" method to be implemented.');
        }

        $this->server();
    }

    private function configPath(): string
    {
        if (!method_exists($this, 'setConfig')) {
            throw new BootloaderException('Bootloader requires the "setConfig" method to be implemented.');
        }

        return $this->setConfig();
    }
}
