<?php

namespace Felora\Foundation\Bootstrap;

use Felora\Filesystem\Path;
use Felora\Support\PathResolver;
use Felora\Contracts\Container\Container;
use Felora\Contracts\Filesystem\Path as PathContract;
use Felora\Contracts\Foundation\Bootstrap\Bootstrap;

class InitialRegistration implements Bootstrap
{
    public function bootstrap(Container $container): void
    {
        $container->instance(PathResolver::class, new PathResolver);
        $container->bind(PathContract::class, function () {
            return new Path([]);
        });

        $this->registerPaths($container);
    }

    protected function registerPaths($container)
    {
        /** @var PathResolver $pathResolver */
        $pathResolver = $container->make(PathResolver::class);
        $baseKey = $pathResolver::BASE_KEY;
        //
        $base = rtrim(BASE).DIRECTORY_SEPARATOR;
        $pathResolver->set($baseKey, $base);
        $pathResolver->set($baseKey.'.config', $base.'config');
        $pathResolver->set($baseKey.'.cache', $base.'z_cache');
    }
}
