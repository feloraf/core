<?php

namespace Felora\Bootloader;

use Felora\Bootstrap\Bootstrap;
use Felora\Contracts\Bootloader\BootloaderException;
use Felora\Foundation\Bootstrap\LoadApplications;

/**
 * @method setUp()
 * @method setDown()
 */
class Bootloader extends Bootstrap
{
    public function __construct()
    {
        $this->handle();
    }

    protected function base(): string
    {
        throw new BootloaderException('Bootloader requires the "setConfig" method to be implemented.');
    }

    private function handle(): void
    {
        define('BASE', $this->base()); 

        if(function_exists('setUp')) {
            $this->setUp();
        }

        $this->loadBootstraps();

        $this->withBootstrap(LoadApplications::class);

        if(function_exists('setDown')) {
            $this->setDown();
        }
    }
}
