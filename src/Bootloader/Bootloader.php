<?php
namespace Felora\Bootloader;

use Felora\Bootloader\Traits\Registery;
use Felora\Container\Container;
use Felora\Contracts\Container\Container as ContainerContract;

class Bootloader
{
    use Registery;

    private ContainerContract $container;

    public function __construct()
    {
        $this->container = new Container;

        $this->handle();
    }

    private function handle(): void
    {
        $this->register();
    }

    private function configPath(): string
    {
        if(! method_exists($this, 'setConfig')) {
            throw new BootloaderException('Please setup your config.php');
        }

        return $this->setConfig();
    }
}