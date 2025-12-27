<?php
namespace Felora\Bootloader;

use Felora\Container\Container;
use Felora\Bootloader\traits\Bootstraping;
use Felora\Bootstrap\LoadApplications;
use Felora\Contracts\Bootloader\BootloaderException;
use Felora\Contracts\Container\Container as ContainerContract;

/**
 * @method setUp()
 * @method setDown()
 */
class Bootloader
{
    use Bootstraping;

    protected ContainerContract $container;

    public function __construct()
    {
        $this->container = new Container;

        $this->handle();
    }

    protected function base(): string
    {
        throw new BootloaderException('Bootloader requires the "setConfig" method to be implemented.');
    }

    private function handle(): void
    {
        $this->container->singleton('base', fn() => $this->base());

        if(function_exists('setUp')) {
            $this->setUp();
        }

        $this->loadBootstraps();

        $this->withBootstrap(LoadApplications::class);

        if(function_exists('setDown')) {
            $this->setUp();
        }
    }
}
