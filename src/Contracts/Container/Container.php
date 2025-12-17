<?php

namespace Felora\Contracts\Container;

/**
 * Minimal IoC Container
 */
interface Container
{
    /**
     * Resolve an abstract from the container
     *
     * @param string $abstract
     * @param array $parameters Optional constructor parameters
     * @return mixed
     * @throws Exception
     */
    public function make(string $abstract, array $parameters = []);

    /**
     * Register a singleton binding
     *
     * @param string $abstract
     * @param \Closure|string|null $concrete
     * @return void
     */
    public function singleton(string $abstract, \Closure|string|null $concrete = null);

    /**
     * Register a binding in the container
     *
     * @param string $abstract
     * @param \Closure|string|null $concrete
     * @param bool $shared
     * @return void
     * @throws Exception
     */
    public function bind(string $abstract, \Closure|string|null $concrete = null, bool $shared = false): void;

    /**
     * Register an existing object instance in the container.
     *
     * If the given abstract was previously bound as a singleton or instance,
     * it will be fully removed and replaced with the provided instance.
     *
     * @param string $abstract The abstract type or identifier
     * @param object $instance The concrete instance to bind
     * @return object The resolved instance from the container
     */
    public function instance(string $abstract, object $instance): object;

    /**
     * Determine if the given abstract is bound in the container.
     *
     * This includes both normal bindings and instance/singleton bindings.
     *
     * @param string $abstract The abstract type or identifier
     * @return bool True if the abstract is bound or has an instance
     */
    public function bound($abstract): bool;
}
