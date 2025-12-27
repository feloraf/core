<?php

namespace Felora\Bootloader\traits;

use Felora\Contracts\Bootstrap\Bootstrap;
use Felora\Bootstrap\ {
    LoadConfig,
    ExceptionHandler,
    InitialRegistration,
};

trait Bootstraping
{
    /**
     * @var array<Bootstrap>
     */
    protected array $bootstraps = [
        0 => InitialRegistration::class,
        2 => LoadConfig::class,
        1 => ExceptionHandler::class,
    ];

    private function loadBootstraps(): void
    {
        foreach($this->bootstraps as $bootstrap) {
            (new $bootstrap)->bootstrap($this->container);
        }
    }
}

