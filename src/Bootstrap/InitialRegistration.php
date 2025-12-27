<?php

namespace Felora\Bootstrap;

use Felora\Filesystem\Path;
use Felora\Contracts\Bootstrap\Bootstrap;
use Felora\Contracts\Container\Container;
use Felora\Contracts\Filesystem\Path as PathContract;

class InitialRegistration implements Bootstrap
{
    public function bootstrap(Container $container): void
    {
        $container->instance(PathContract::class, new Path([]));
    }
}
