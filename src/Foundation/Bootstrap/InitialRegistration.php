<?php

namespace Felora\Foundation\Bootstrap;

use Felora\Filesystem\Path;
use Felora\Contracts\Container\Container;
use Felora\Contracts\Filesystem\Path as PathContract;
use Felora\Contracts\Foundation\Bootstrap\Bootstrap;

class InitialRegistration implements Bootstrap
{
    public function bootstrap(Container $container): void
    {
        $container->instance(PathContract::class, new Path([]));
    }
}
