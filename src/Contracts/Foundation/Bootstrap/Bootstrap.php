<?php

namespace Felora\Contracts\Foundation\Bootstrap;

use Felora\Contracts\Container\Container;

interface Bootstrap
{
    public function bootstrap(Container $container): void;
}
