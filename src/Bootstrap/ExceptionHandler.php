<?php

namespace Felora\Bootstrap;

use Felora\Contracts\Bootstrap\Bootstrap;
use Felora\Contracts\Container\Container;

class ExceptionHandler implements Bootstrap
{
    public function bootstrap(Container $container): void
    {
        // set_error_handler();
        // set_exception_handler();
        // register_shutdown_function();
    }
}
