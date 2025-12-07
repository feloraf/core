<?php

namespace Felora\Container;

use Exception;
use Felora\Contracts\Container\ContainerContracts;

class Container implements ContainerContracts
{
    private static $bindings = [];

    private static $shared = [];

    private static $resolved = [];

    protected function bindings(): array
    {
        return static::$bindings;
    }

    protected function setBind($abstract, $concrete)
    {
        static::$bindings[$abstract] = $concrete;
    }

    protected function resolved(): array
    {
        return static::$resolved;
    }

    protected function setResolve($abstract, $solved)
    {
        static::$resolved[$abstract] = $solved;
    }

    public function unsetResolve($abstract)
    {
        if(array_key_exists($abstract, static::$resolved)) {
            unset(static::$resolved[$abstract]);
        }
    }

    protected function shared(): array
    {
        return static::$shared;
    }

    protected function setShare($abstract)
    {
        static::$shared[] = $abstract;
    }

    protected function isShared($abstract): bool
    {
        return in_array($abstract, $this->shared()) ? true : false;
    }

    /**
     * @param string $abstract
     * @param array $parameters
     */
    public function make($abstract, $parameters = [])
    {
        if(! array_key_exists($abstract ,$this->bindings())) {
            throw new \Exception("Error message");
        }

        return $this->resolve($this->bindings()[$abstract], $abstract);
    }

    /**
     * @param string $abstract
     * @param \Closure|string|null $concrete
     */
    protected function resolve($concrete, $abstract)
    {
        if(array_key_exists($abstract, $this->resolved())) {
            return $this->resolved()[$abstract];
        }

        if(is_callable($concrete)) {
            $resolved = ($concrete)($this);
        }

        if(is_string($concrete)) {
            if(! class_exists($concrete)) {
                $resolved = $concrete;
            } else {
                $resolved = new $concrete;
            }
        }

        if($this->isShared($abstract)) {
            $this->setResolve($abstract, $resolved);
        }

        return $resolved;
    }

    /**
     * @param string $abstract
     * @param \Closure|string|null $concrete
     */
    public function singleton($abstract, $concrete = null): void
    {
        $this->bind($abstract, $concrete, $shared=true);
    }

    /**
     * @param string $abstract
     * @param \Closure|string|null $concrete
     * @param bool $shared
     */
    public function bind($abstract, $concrete = null, $shared = false): void
    {
        if(! is_string($abstract)) {
            throw new \Exception("The abstract must be a string");
        }

        if($shared) {
            $this->setShare($abstract);
            $this->unsetResolve($abstract);
        }

        $concreteIsNull = is_null($concrete);

        if(! $concreteIsNull) {
            $this->setBind($abstract, $concrete);

            return;
        }

        $this->setBind($abstract, $abstract);
    }

    public static function __callStatic($name, $arguments)
    {
        return (new static)->{$name}(...$arguments);
    }
}
