<?php

namespace Felora\Foundation\Bootstrap;

use Closure;
use Felora\Contracts\Container\Container;
use Felora\Contracts\Foundation\Bootstrap\Bootstrap;

class ExceptionHandler implements Bootstrap
{
    public function bootstrap(Container $container): void
    {
        set_error_handler($this->forwardTo('errorHandler'));
        //
        set_exception_handler($this->forwardTo('exceptionHandler'));
        //
        register_shutdown_function($this->forwardTo('registerShutdown'));
    }

    protected function forwardTo(string $method): Closure
    {
        return function(...$argc) use ($method) {
            ($this->{$method})(...$argc);
        };
    }

    protected function errorHandler() {
        //
    }

    protected function exceptionHandler() {
        //
    }

    protected function registerShutdown() {
        //
    }
}
