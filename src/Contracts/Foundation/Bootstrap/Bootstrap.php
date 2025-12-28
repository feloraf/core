<?php

namespace Felora\Contracts\Foundation\Bootstrap;

use Felora\Contracts\Container\Container;

/**
 * Interface Bootstrap
 *
 * Represents a framework bootstrapper.
 *
 * Each bootstrap class (e.g. LoadConfig, ExceptionHandler, InitialRegistration)
 * is responsible for bootstrapping a specific part of the framework
 * and will be executed by the main Bootloader/Bootstrap manager.
 *
 * @package Felora\Contracts\Foundation\Bootstrap
 */
interface Bootstrap
{
    /**
     * Bootstrap the application.
     *
     * This method is called during the bootstrapping phase and receives
     * the main service container instance.
     *
     * @param Container $container
     * @return void
     */
    public function bootstrap(Container $container): void;
}
