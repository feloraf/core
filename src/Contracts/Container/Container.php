<?php

namespace Felora\Contracts\Container;

interface Container
{
    public function instance(string $abstract, object $instance);

    public function make(string $abstract, array $parameters = []);

    public function singleton(string $abstract, \Closure|string|null $concrete = null);

    public function bind(string $abstract, \Closure|string|null $concrete = null, bool $shared = false);
}
